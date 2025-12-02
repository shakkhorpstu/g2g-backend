<?php

namespace Modules\Profile\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Profile\Contracts\Repositories\ProfileRepositoryInterface;
use Modules\Profile\Repositories\ProfileRepository;
use Modules\Profile\Contracts\Repositories\UserProfileRepositoryInterface;
use Modules\Profile\Repositories\UserProfileRepository;
use Modules\Profile\Contracts\Repositories\PswProfileRepositoryInterface;
use Modules\Profile\Repositories\PswProfileRepository;

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
        
        // Bind User Profile Repository
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);
        
        // Bind PSW Profile Repository
        $this->app->bind(PswProfileRepositoryInterface::class, PswProfileRepository::class);

        // Bind Admin Profile Repository
        $this->app->bind(
            \Modules\Profile\Contracts\Repositories\AdminProfileRepositoryInterface::class,
            \Modules\Profile\Repositories\AdminProfileRepository::class
        );

        // Bind PSW Service Category Repository
        $this->app->bind(
            \Modules\Profile\Contracts\Repositories\PswServiceCategoryRepositoryInterface::class,
            \Modules\Profile\Repositories\PswServiceCategoryRepository::class
        );

        // Bind PSW Availability Repository
        $this->app->bind(
            \Modules\Profile\Contracts\Repositories\PswAvailabilityRepositoryInterface::class,
            \Modules\Profile\Repositories\PswAvailabilityRepository::class
        );

        // Bind Document Type Repository
        $this->app->bind(
            \Modules\Profile\Contracts\Repositories\DocumentTypeRepositoryInterface::class,
            \Modules\Profile\Repositories\DocumentTypeRepository::class
        );

        // Bind Profile Document Repository
        $this->app->bind(
            \Modules\Profile\Contracts\Repositories\ProfileDocumentRepositoryInterface::class,
            \Modules\Profile\Repositories\ProfileDocumentRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}