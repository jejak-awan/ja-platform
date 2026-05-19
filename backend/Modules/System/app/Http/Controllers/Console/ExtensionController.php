<?php

declare(strict_types=1);

namespace Modules\System\Http\Controllers\Console;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Models\Extension;
use Modules\System\Models\ExtensionLog;
use Modules\System\Models\Feature;
use Modules\System\Services\ExtensionSecurityScanner;
use ZipArchive;

class ExtensionController extends BaseApiController
{
    /**
     * List all extensions (combining database statuses and physical folder discovery).
     */
    public function index(): JsonResponse
    {
        $this->discoverExtensions();

        $extensions = Extension::with('features')->latest()->get();

        return $this->success($extensions, 'Extensions retrieved successfully');
    }

    /**
     * Activate a module/plugin.
     */
    public function activate(string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        if ($extension->status === 'active') {
            return $this->error('Extension is already active');
        }

        $versionBefore = $extension->version;

        try {
            // 1. Run dynamic migrations if any exist in the package folder
            $migrationPath = $extension->type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $extension->slug))).'/database/migrations')
                : base_path('Plugins/'.$extension->slug.'/database/migrations');

            if (is_dir($migrationPath)) {
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path().'/', '', $migrationPath),
                    '--force' => true,
                ]);
            }

            // 2. Trigger onActivate lifecycle event/hook
            \Hook::action('extension_activated', $extension);

            // 3. Update status
            $extension->update([
                'status' => 'active',
                'database_version' => $extension->version,
            ]);

            ExtensionLog::create([
                'extension_slug' => $extension->slug,
                'action' => 'activate',
                'version_before' => $versionBefore,
                'version_after' => $extension->version,
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension activated successfully');

        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $extension->slug,
                'action' => 'activate',
                'version_before' => $versionBefore,
                'version_after' => $extension->version,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'performed_by' => auth()->id(),
            ]);

            return $this->error('Failed to activate extension: '.$e->getMessage());
        }
    }

    /**
     * Deactivate a module/plugin.
     */
    public function deactivate(string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        if ($extension->is_core) {
            return $this->error('Core modules cannot be deactivated');
        }

        if ($extension->status !== 'active') {
            return $this->error('Extension is not active');
        }

        try {
            // 1. Trigger onDeactivate lifecycle event/hook
            \Hook::action('extension_deactivated', $extension);

            // 2. Update status
            $extension->update(['status' => 'inactive']);

            ExtensionLog::create([
                'extension_slug' => $extension->slug,
                'action' => 'deactivate',
                'version_before' => $extension->version,
                'version_after' => $extension->version,
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension deactivated successfully');

        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $extension->slug,
                'action' => 'deactivate',
                'version_before' => $extension->version,
                'version_after' => $extension->version,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'performed_by' => auth()->id(),
            ]);

            return $this->error('Failed to deactivate extension: '.$e->getMessage());
        }
    }

    /**
     * Update configuration settings for an extension.
     */
    public function updateSettings(Request $request, string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $extension->update(['settings' => $validated['settings']]);

        return $this->success($extension, 'Extension settings updated successfully');
    }

    /**
     * Uninstall and physically delete an extension.
     */
    public function uninstall(string $slug): JsonResponse
    {
        $extension = Extension::where('slug', $slug)->firstOrFail();

        if ($extension->is_core) {
            return $this->error('Core modules cannot be uninstalled');
        }

        if ($extension->status === 'active') {
            $this->deactivate($extension->slug);
        }

        try {
            // 1. Trigger onUninstall lifecycle event/hook
            \Hook::action('extension_uninstalled', $extension);

            // 2. Delete physical folder files
            $folderPath = $extension->type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $extension->slug))))
                : base_path('Plugins/'.$extension->slug);

            if (is_dir($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            // 3. Remove DB record
            $extension->forceDelete();

            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'uninstall',
                'version_before' => $extension->version,
                'version_after' => null,
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success(null, 'Extension uninstalled and files deleted successfully');

        } catch (Exception $e) {
            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'uninstall',
                'version_before' => $extension->version,
                'version_after' => null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'performed_by' => auth()->id(),
            ]);

            return $this->error('Failed to uninstall extension: '.$e->getMessage());
        }
    }

    /**
     * Upload an extension ZIP package, perform security scans, extract, and register.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:51200', // max 50MB
        ]);

        $uploadedFile = $request->file('file');
        $tempPath = $uploadedFile->getPathname();

        try {
            // 1. Run our Static Security AST Scanner
            (new ExtensionSecurityScanner())->scanZip($tempPath);

            // 2. Extract temporarily to inspect manifest.json
            $zip = new ZipArchive;
            if ($zip->open($tempPath) !== true) {
                return $this->error('Failed to open uploaded ZIP file.');
            }

            $manifestContent = null;
            $manifestIndex = -1;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (basename($filename) === 'manifest.json') {
                    $manifestContent = $zip->getFromIndex($i);
                    $manifestIndex = $i;
                    break;
                }
            }

            if (! $manifestContent) {
                $zip->close();

                return $this->error('Security gate: Missing manifest.json in package.');
            }

            $manifest = json_decode($manifestContent, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! isset($manifest['slug'], $manifest['type'], $manifest['name'], $manifest['version'])) {
                $zip->close();

                return $this->error('Security gate: Invalid manifest.json schema.');
            }

            $slug = $manifest['slug'];
            $type = $manifest['type'];

            if (! in_array($type, ['module', 'plugin'])) {
                $zip->close();

                return $this->error('Security gate: Invalid extension type in manifest.');
            }

            // 3. Perform target directory extraction
            $targetDir = $type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))))
                : base_path('Plugins/'.$slug);

            if (is_dir($targetDir)) {
                $zip->close();

                return $this->error('An extension with this slug already exists.');
            }

            File::makeDirectory($targetDir, 0755, true, true);
            $zip->extractTo($targetDir);
            $zip->close();

            // 4. Register in database
            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $type,
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'database_version' => '0.0.0',
                    'status' => 'inactive',
                    'is_core' => false,
                    'author' => $manifest['author'] ?? 'Anonymous',
                    'license' => $manifest['license'] ?? 'MIT',
                    'requirements' => $manifest['dependencies'] ?? [],
                    'settings' => [],
                ]
            );

            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'install',
                'version_before' => null,
                'version_after' => $manifest['version'],
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension uploaded and installed successfully', 201);

        } catch (Exception $e) {
            return $this->error('Failed to upload/install package: '.$e->getMessage());
        }
    }



    /**
     * Automatically discover and synchronize newly added folders in Modules/ and Plugins/
     */
    protected function discoverExtensions(): void
    {
        $discovered = [];

        // 1. Scan Modules
        $modulesPath = base_path('Modules');
        if (is_dir($modulesPath)) {
            $moduleDirs = File::directories($modulesPath);
            foreach ($moduleDirs as $dir) {
                $manifestFile = $dir.'/manifest.json';
                // Fallback for modular module.json if no manifest exists yet
                if (! File::exists($manifestFile)) {
                    $manifestFile = $dir.'/module.json';
                }

                if (File::exists($manifestFile)) {
                    $manifest = json_decode(File::get($manifestFile), true);
                    $slug = $manifest['slug'] ?? $manifest['alias'] ?? null;
                    if ($manifest && $slug) {
                        $discovered[$slug] = [
                            'type' => 'module',
                            'name' => $manifest['name'] ?? basename($dir),
                            'version' => $manifest['version'] ?? '1.0.0',
                            'author' => $manifest['author'] ?? 'Core',
                            'is_core' => in_array($slug, ['system', 'security', 'analytics', 'infra', 'ai', 'media', 'cms', 'school']),
                            'features' => $manifest['features'] ?? [],
                        ];
                    }
                }
            }
        }

        // 2. Scan Plugins
        $pluginsPath = base_path('Plugins');
        if (is_dir($pluginsPath)) {
            $pluginDirs = File::directories($pluginsPath);
            foreach ($pluginDirs as $dir) {
                $manifestFile = $dir.'/manifest.json';
                if (File::exists($manifestFile)) {
                    $manifest = json_decode(File::get($manifestFile), true);
                    if ($manifest && isset($manifest['slug'])) {
                        $discovered[$manifest['slug']] = [
                            'type' => 'plugin',
                            'name' => $manifest['name'] ?? basename($dir),
                            'version' => $manifest['version'] ?? '1.0.0',
                            'author' => $manifest['author'] ?? 'Anonymous',
                            'is_core' => false,
                            'features' => $manifest['features'] ?? [],
                        ];
                    }
                }
            }
        }

        // 3. Synchronize with Database
        foreach ($discovered as $slug => $meta) {
            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $meta['type'],
                    'name' => $meta['name'],
                    'version' => $meta['version'],
                    'database_version' => Extension::where('slug', $slug)->value('database_version') ?? '1.0.0',
                    'status' => Extension::where('slug', $slug)->value('status') ?? ($meta['is_core'] ? 'active' : 'inactive'),
                    'is_core' => $meta['is_core'],
                    'author' => 'jejakawan',
                    'license' => 'Proprietary',
                    'requirements' => [],
                ]
            );

            // Synchronize sub-features
            if (! empty($meta['features']) && is_array($meta['features'])) {
                foreach ($meta['features'] as $feat) {
                    if (isset($feat['slug'], $feat['name'])) {
                        Feature::updateOrCreate(
                            ['slug' => $feat['slug']],
                            [
                                'extension_slug' => $slug,
                                'name' => $feat['name'],
                                'description' => $feat['description'] ?? null,
                                'category' => $feat['category'] ?? 'business',
                                'is_active' => Feature::where('slug', $feat['slug'])->value('is_active') ?? true,
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Toggle status of a specific sub-feature (Activate/Deactivate).
     */
    public function toggleFeature(Request $request, string $slug): JsonResponse
    {
        $feature = Feature::where('slug', $slug)->firstOrFail();
        $extension = $feature->extension;

        if ($extension && $extension->is_core && in_array($extension->slug, ['system', 'security', 'infra'])) {
            return $this->error('Sub-features of critical core modules cannot be toggled');
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $feature->update([
            'is_active' => $validated['is_active'],
        ]);

        return $this->success($feature, 'Sub-feature status updated successfully');
    }

    /**
     * Clone a plugin or module from a Git repository, scan it, and register it.
     */
    public function gitClone(Request $request): JsonResponse
    {
        $request->validate([
            'repo_url' => 'required|string',
        ]);

        $repoUrl = $request->input('repo_url');

        // Simple validation of git URL
        if (!preg_match('/^(https?:\/\/|git@|ssh:\/\/)/', $repoUrl)) {
            return $this->error('Security gate: Invalid Git repository URL format.');
        }

        // Create a temporary directory inside storage/framework
        $tempDirName = 'git-clone-' . uniqid();
        $tempPath = base_path('storage/framework/' . $tempDirName);

        try {
            // 1. Run git clone into temporary path
            $escapedRepo = escapeshellarg($repoUrl);
            $escapedPath = escapeshellarg($tempPath);
            
            $output = [];
            $resultCode = 0;
            exec("git clone --depth 1 {$escapedRepo} {$escapedPath} 2>&1", $output, $resultCode);

            if ($resultCode !== 0) {
                if (is_dir($tempPath)) {
                    File::deleteDirectory($tempPath);
                }
                $errorStr = implode("\n", $output);
                return $this->error('Failed to clone Git repository: ' . $errorStr);
            }

            // 2. Perform Static Security Scan on cloned PHP files using AST parser
            (new ExtensionSecurityScanner())->scanDirectory($tempPath);

            // 3. Verify manifest.json exists
            $manifestFile = $tempPath . '/manifest.json';
            if (!File::exists($manifestFile)) {
                File::deleteDirectory($tempPath);
                return $this->error('Security gate: Cloned repository is missing manifest.json.');
            }

            $manifest = json_decode(File::get($manifestFile), true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($manifest['slug'], $manifest['type'], $manifest['name'], $manifest['version'])) {
                File::deleteDirectory($tempPath);
                return $this->error('Security gate: Invalid manifest.json schema in Git repository.');
            }

            $slug = $manifest['slug'];
            $type = $manifest['type'];

            if (!in_array($type, ['module', 'plugin'])) {
                File::deleteDirectory($tempPath);
                return $this->error('Security gate: Invalid extension type in manifest.');
            }

            // 4. Move cloned directory to its final location
            $targetDir = $type === 'module'
                ? base_path('Modules/' . str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))))
                : base_path('Plugins/' . $slug);

            if (is_dir($targetDir)) {
                File::deleteDirectory($tempPath);
                return $this->error('An extension with this slug already exists.');
            }

            // Move the cloned repository
            File::moveDirectory($tempPath, $targetDir);

            // 5. Register in database
            $extension = Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $type,
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'database_version' => '0.0.0',
                    'status' => 'inactive',
                    'is_core' => false,
                    'author' => $manifest['author'] ?? 'Anonymous',
                    'license' => $manifest['license'] ?? 'MIT',
                    'requirements' => $manifest['dependencies'] ?? [],
                    'settings' => [],
                ]
            );

            // Dynamically register sub-features from manifest if present
            if (!empty($manifest['features']) && is_array($manifest['features'])) {
                foreach ($manifest['features'] as $feat) {
                    if (isset($feat['slug'], $feat['name'])) {
                        Feature::updateOrCreate(
                            ['slug' => $feat['slug']],
                            [
                                'extension_slug' => $slug,
                                'name' => $feat['name'],
                                'description' => $feat['description'] ?? null,
                                'category' => $feat['category'] ?? 'business',
                                'is_active' => true,
                            ]
                        );
                    }
                }
            }

            ExtensionLog::create([
                'extension_slug' => $slug,
                'action' => 'install',
                'version_before' => null,
                'version_after' => $manifest['version'],
                'status' => 'success',
                'performed_by' => auth()->id(),
            ]);

            return $this->success($extension, 'Extension cloned and installed successfully from Git repository!', 201);

        } catch (Exception $e) {
            if (is_dir($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            return $this->error('Failed to clone and install: ' . $e->getMessage());
        }
    }


}
