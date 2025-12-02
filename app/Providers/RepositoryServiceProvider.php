<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Global Repository Service Provider
 * 
 * This provider is reserved for global/shared repository bindings.
 * Module-specific repositories are bound in their respective module providers.
 * 
 * Use this provider for:
 * - Cross-module shared repositories
 * - Global utility repositories
 * - Third-party service repositories
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register global/shared repository bindings here
        // Module-specific repositories are handled in their respective modules
        
        // File Storage Repository (used across modules)
        $this->app->bind(
            \App\Shared\Contracts\Repositories\FileStorageRepositoryInterface::class,
            \App\Shared\Repositories\FileStorageRepository::class
        );
        
        // Note: Authentication repositories are now handled in Core module
        // See: Modules/Core/app/Providers/CoreServiceProvider.php
        
        // Example for other global repositories:
        // $this->app->bind(GlobalConfigRepositoryInterface::class, GlobalConfigRepository::class);
        // $this->app->bind(SystemLogRepositoryInterface::class, SystemLogRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}