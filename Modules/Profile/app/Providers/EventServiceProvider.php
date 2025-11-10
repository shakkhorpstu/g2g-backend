<?php

namespace Modules\Profile\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Core\Events\UserRegistered::class => [
            \Modules\Profile\Listeners\CreateUserProfile::class,
        ],
        \Modules\Core\Events\PswRegistered::class => [
            \Modules\Profile\Listeners\CreatePswProfile::class,
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
