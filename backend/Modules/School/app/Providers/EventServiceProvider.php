<?php

declare(strict_types=1);

namespace Modules\School\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\School\Events\AttendanceMarked;
use Modules\School\Events\StudentCreated;
use Modules\School\Events\StudentDeleted;
use Modules\School\Listeners\LogAcademicActivity;
use Modules\School\Listeners\LogStudentActivity;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        StudentCreated::class => [
            LogStudentActivity::class.'@handleStudentCreated',
        ],
        StudentDeleted::class => [
            LogStudentActivity::class.'@handleStudentDeleted',
        ],

        AttendanceMarked::class => [
            LogAcademicActivity::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
