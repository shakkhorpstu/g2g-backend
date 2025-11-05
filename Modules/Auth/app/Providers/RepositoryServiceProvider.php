<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Contracts\Repositories\UserRepositoryInterface;
use Modules\Auth\Repositories\UserRepository;

/**
 * Auth Repository Service Provider
 * 
 * Handles dependency injection bindings for Auth module repositories.
 * This provider binds repository interfaces to their concrete implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Auth Repository Interfaces to their Implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        
        // Bind Auth Services
        $this->app->bind(\Modules\Auth\Services\AuthService::class);
        
        // Add more Auth module repository bindings here as needed
        // Example:
        // $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
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