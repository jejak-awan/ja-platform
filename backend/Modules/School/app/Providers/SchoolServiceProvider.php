<?php

namespace Modules\School\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\Attendance;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Academic\Schedule;
use Modules\School\Models\Academic\Semester;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Subject;
use Modules\School\Models\Academic\TeachingJournal;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Logistics\Building;
use Modules\School\Models\Logistics\LandAsset;
use Modules\School\Models\Logistics\MaintenanceTicket;
use Modules\School\Models\Logistics\Room;
use Modules\School\Models\Logistics\SchoolAsset;
use Modules\School\Models\Operations\Visitor;
use Modules\School\Models\Student\Achievement;
use Modules\School\Models\Student\CounselingRecord;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Student\Violation;
use Modules\School\Policies\AcademicPolicy;
use Modules\School\Policies\OperationsAttendancePolicy;
use Modules\School\Policies\OperationsStudentAffairsPolicy;
use Modules\School\Policies\OperationsVisitorPolicy;
use Modules\School\Policies\SarprasResourcePolicy;
use Modules\School\Policies\SchoolPolicy;
use Modules\School\Policies\StaffPolicy;
use Modules\School\Policies\StudentPolicy;
use Modules\School\Services\SchoolWorkspaceResolver;
use Modules\System\Contracts\LayoutRegistryInterface;
use Modules\System\Contracts\WorkspaceResolver;
use Modules\System\Models\User;
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
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->registerUserModuleIntegrations();
    }

    /**
     * Register School-specific integrations to Core User model.
     */
    protected function registerUserModuleIntegrations(): void
    {
        // 1. Register School-specific role ranks
        User::registerRoleRanks([
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
        User::resolveRelationUsing('staff', fn ($userModel) => $userModel->hasMany(Staff::class));

        User::resolveRelationUsing('levels', fn ($userModel) => $userModel->hasManyThrough(
            SchoolUnit::class,
            Staff::class,
            'user_id',
            'id',
            'id',
            'workspace_id'
        ));

        // 3. Register Layout Locations
        if ($this->app->bound(LayoutRegistryInterface::class)) {
            $registry = $this->app->make(LayoutRegistryInterface::class);
            $registry->registerMenuLocations('school', [
                'portal-header', 'portal-footer', 'portal-sidebar',
            ]);
            $registry->registerWidgetLocations('school', [
                'portal-sidebar', 'portal-footer',
            ]);
        }
    }

    /**
     * Register module policies.
     */
    protected function registerPolicies(): void
    {
        $academicPolicy = AcademicPolicy::class;

        Gate::policy(School::class, SchoolPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);

        Gate::policy(AcademicYear::class, $academicPolicy);
        Gate::policy(Semester::class, $academicPolicy);
        Gate::policy(Subject::class, $academicPolicy);
        Gate::policy(StudyGroup::class, $academicPolicy);
        Gate::policy(Schedule::class, $academicPolicy);
        Gate::policy(TeachingJournal::class, $academicPolicy);
        Gate::policy(Department::class, $academicPolicy);

        Gate::policy(Attendance::class, OperationsAttendancePolicy::class);
        Gate::policy(Violation::class, OperationsStudentAffairsPolicy::class);
        Gate::policy(Achievement::class, OperationsStudentAffairsPolicy::class);
        Gate::policy(CounselingRecord::class, OperationsStudentAffairsPolicy::class);
        Gate::policy(Visitor::class, OperationsVisitorPolicy::class);

        $sarprasPolicy = SarprasResourcePolicy::class;
        Gate::policy(LandAsset::class, $sarprasPolicy);
        Gate::policy(Building::class, $sarprasPolicy);
        Gate::policy(Room::class, $sarprasPolicy);
        Gate::policy(SchoolAsset::class, $sarprasPolicy);
        Gate::policy(MaintenanceTicket::class, $sarprasPolicy);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Bind Workspace Resolver
        $this->app->bind(WorkspaceResolver::class, SchoolWorkspaceResolver::class);
        $this->app->alias(WorkspaceResolver::class, 'workspace.resolver');
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
            $this->loadTranslationsFrom((string) module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom((string) module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        /** @var string $configPathPart */
        $configPathPart = config('modules.paths.generator.config.path');
        $configPath = (string) module_path($this->name, $configPathPart);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            /** @var \SplFileInfo $file */
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

        config([$key => array_replace_recursive((array) $existing, (array) $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = (string) module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        /** @var string|null $namespaceConfig */
        $namespaceConfig = config('modules.namespace');
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
