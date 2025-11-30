<?php

namespace Modules\Profile\Listeners;

use Modules\Core\Events\UserRegistered;
use Modules\Profile\Services\UserProfileService;
use Modules\Profile\Models\NotificationSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Create User Profile Listener
 * 
 * Listens to UserRegistered event and creates initial profile for the user.
 * This listener runs synchronously for now.
 */
class CreateUserProfile
{

    /**
     * User profile service instance
     *
     * @var UserProfileService
     */
    private UserProfileService $userProfileService;

    /**
     * Create the event listener
     *
     * @param UserProfileService $userProfileService
     */
    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    /**
     * Handle the event
     *
     * @param UserRegistered $event
     * @return void
     */
    public function handle(UserRegistered $event): void
    {
        try {
            // Create initial profile for the registered user
            $this->userProfileService->createInitialProfile($event->user->id, []);

            // Create initial notification settings for the user
            NotificationSetting::createDefaults($event->user);

            Log::info('User profile and notification settings created successfully', [
                'user_id' => $event->user->id,
                'email' => $event->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create user profile or notification settings', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger retry mechanism if using queues
            throw $e;
        }
    }

    /**
     * Handle a job failure
     *
     * @param UserRegistered $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::critical('User profile creation failed permanently', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'error' => $exception->getMessage()
        ]);
    }
}