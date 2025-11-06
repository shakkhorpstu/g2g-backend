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
        
        // Core authentication repository (infrastructure level)
        $this->app->bind(
            \App\Contracts\Repositories\AuthRepositoryInterface::class, 
            \App\Repositories\AuthRepository::class
        );
        
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