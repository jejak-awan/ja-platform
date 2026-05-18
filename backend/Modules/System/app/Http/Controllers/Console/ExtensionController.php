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
use ZipArchive;

class ExtensionController extends BaseApiController
{
    /**
     * List all extensions (combining database statuses and physical folder discovery).
     */
    public function index(): JsonResponse
    {
        $this->discoverExtensions();

        $extensions = Extension::latest()->get();

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
            // 1. Run our Static Security Regex Scanner
            $this->scanZipContents($tempPath);

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
     * Run static regex checks on all ZIP PHP files before extraction (Sandbox Guard).
     */
    protected function scanZipContents(string $zipPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Gagal membuka file ZIP.');
        }

        $bannedPatterns = [
            '/\bexec\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/\bpassthru\s*\(/i',
            '/\beval\s*\(/i',
            '/\bbase64_decode\s*\(/i',
            '/`[^`]*`/', // Backticks execution operator
        ];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (pathinfo($filename, PATHINFO_EXTENSION) === 'php') {
                $content = $zip->getFromIndex($i);

                foreach ($bannedPatterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $zip->close();
                        throw new Exception("Security Gate: File {$filename} mengandung kode/fungsi terlarang.");
                    }
                }
            }
        }

        $zip->close();
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
                        ];
                    }
                }
            }
        }

        // 3. Synchronize with Database
        foreach ($discovered as $slug => $meta) {
            Extension::updateOrCreate(
                ['slug' => $slug],
                [
                    'type' => $meta['type'],
                    'name' => $meta['name'],
                    'version' => $meta['version'],
                    'database_version' => Extension::where('slug', $slug)->value('database_version') ?? '1.0.0',
                    'status' => Extension::where('slug', $slug)->value('status') ?? ($meta['is_core'] ? 'active' : 'inactive'),
                    'is_core' => $meta['is_core'],
                    'author' => $meta['author'],
                    'license' => 'MIT',
                    'requirements' => [],
                ]
            );
        }
    }
}
