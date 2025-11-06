<?php

namespace Modules\Profile\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Profile\Contracts\Repositories\ProfileRepositoryInterface;
use Modules\Profile\Repositories\ProfileRepository;

/**
 * Profile Repository Service Provider
 * 
 * Handles dependency injection bindings for Profile module repositories.
 * This provider binds repository interfaces to their concrete implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Profile Repository Interfaces to their Implementations
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        
        // Add more Profile module repository bindings here as needed
        // Example:
        // $this->app->bind(ProfileSettingsRepositoryInterface::class, ProfileSettingsRepository::class);
        // $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        // $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}