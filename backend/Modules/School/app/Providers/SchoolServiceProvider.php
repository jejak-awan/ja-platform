<?php

namespace Modules\School\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SchoolServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'School';

    protected string $nameLower = 'school';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerPolicies();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        $this->registerUserModuleIntegrations();
    }

    /**
     * Register School-specific integrations to Core User model.
     */
    protected function registerUserModuleIntegrations(): void
    {
        // 1. Register School-specific role ranks
        \Modules\Core\Models\User::registerRoleRanks([
            'admin-yayasan' => 90,
            'operator-yayasan' => 85,
            'admin-unit' => 80,
            'operator-unit' => 70,
            'staff' => 50,
            'guru' => 50,
            'wali-kelas' => 55,
            'siswa' => 10,
            'student' => 10,
            'orang-tua' => 5,
            'parent' => 5,
        ]);

        // 2. Register dynamic relationships
        \Modules\Core\Models\User::resolveRelationUsing('staff', function ($userModel) {
            return $userModel->hasMany(\Modules\School\Models\HR\Staff::class);
        });

        \Modules\Core\Models\User::resolveRelationUsing('levels', function ($userModel) {
            return $userModel->hasManyThrough(
                \Modules\School\Models\Institution\SchoolUnit::class,
                \Modules\School\Models\HR\Staff::class,
                'user_id',
                'id',
                'id',
                'workspace_id'
            );
        });
    }

    /**
     * Register module policies.
     */
    protected function registerPolicies(): void
    {
        $academicPolicy = \Modules\School\Policies\AcademicPolicy::class;

        Gate::policy(\Modules\School\Models\Institution\School::class, \Modules\School\Policies\SchoolPolicy::class);
        Gate::policy(\Modules\School\Models\Student\Student::class, \Modules\School\Policies\StudentPolicy::class);
        Gate::policy(\Modules\School\Models\HR\Staff::class, \Modules\School\Policies\StaffPolicy::class);



        Gate::policy(\Modules\School\Models\Academic\AcademicYear::class, $academicPolicy);
        Gate::policy(\Modules\School\Models\Academic\Semester::class, $academicPolicy);
        Gate::policy(\Modules\School\Models\Academic\Subject::class, $academicPolicy);
        Gate::policy(\Modules\School\Models\Academic\StudyGroup::class, $academicPolicy);
        Gate::policy(\Modules\School\Models\Academic\Schedule::class, $academicPolicy);
        Gate::policy(\Modules\School\Models\Academic\TeachingJournal::class, $academicPolicy);
        Gate::policy(\Modules\School\Models\Academic\Department::class, $academicPolicy);



        Gate::policy(\Modules\School\Models\Academic\Attendance::class, \Modules\School\Policies\OperationsAttendancePolicy::class);
        Gate::policy(\Modules\School\Models\Student\Violation::class, \Modules\School\Policies\OperationsStudentAffairsPolicy::class);
        Gate::policy(\Modules\School\Models\Student\Achievement::class, \Modules\School\Policies\OperationsStudentAffairsPolicy::class);
        Gate::policy(\Modules\School\Models\Student\CounselingRecord::class, \Modules\School\Policies\OperationsStudentAffairsPolicy::class);
        Gate::policy(\Modules\School\Models\Operations\Visitor::class, \Modules\School\Policies\OperationsVisitorPolicy::class);

        $sarprasPolicy = \Modules\School\Policies\SarprasResourcePolicy::class;
        Gate::policy(\Modules\School\Models\Logistics\LandAsset::class, $sarprasPolicy);
        Gate::policy(\Modules\School\Models\Logistics\Building::class, $sarprasPolicy);
        Gate::policy(\Modules\School\Models\Logistics\Room::class, $sarprasPolicy);
        Gate::policy(\Modules\School\Models\Logistics\SchoolAsset::class, $sarprasPolicy);
        Gate::policy(\Modules\School\Models\Logistics\MaintenanceTicket::class, $sarprasPolicy);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Bind Workspace Resolver
        $this->app->bind(\Modules\Core\Contracts\WorkspaceResolver::class, \Modules\School\Services\SchoolWorkspaceResolver::class);
        $this->app->alias(\Modules\Core\Contracts\WorkspaceResolver::class, 'workspace.resolver');
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        /** @var string $configPathPart */
        $configPathPart = config('modules.paths.generator.config.path');
        /** @var string $configPath */
        $configPath = module_path($this->name, $configPathPart);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    /** @var string $config */
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    /** @var string[] $segments */
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        /** @var array<string, mixed> $existing */
        $existing = (array) config($key, []);
        /** @var array<string, mixed> $module_config */
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        /** @var string|null $namespaceConfig */
        $namespaceConfig = config('modules.namespace');
        /** @var string $namespace */
        $namespace = (string) $namespaceConfig;
        Blade::componentNamespace($namespace.'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        /** @var string[] $viewPaths */
        $viewPaths = (array) config('view.paths');
        foreach ($viewPaths as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
