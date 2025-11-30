<?php

namespace Modules\Profile\Listeners;

use Modules\Core\Events\PswRegistered;
use Modules\Profile\Services\PswProfileService;
use Modules\Profile\Models\NotificationSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Create PSW Profile Listener
 * 
 * Listens to PswRegistered event and creates initial profile for the PSW.
 * This listener runs synchronously for now.
 */
class CreatePswProfile
{

    /**
     * PSW profile service instance
     *
     * @var PswProfileService
     */
    private PswProfileService $pswProfileService;

    /**
     * Create the event listener
     *
     * @param PswProfileService $pswProfileService
     */
    public function __construct(PswProfileService $pswProfileService)
    {
        $this->pswProfileService = $pswProfileService;
    }

    /**
     * Handle the event
     *
     * @param PswRegistered $event
     * @return void
     */
    public function handle(PswRegistered $event): void
    {
        try {
            // Create initial profile for the registered PSW
            $this->pswProfileService->createInitialProfile($event->psw->id, []);

            // Create initial notification settings for the PSW
            NotificationSetting::createDefaults($event->psw);

            Log::info('PSW profile and notification settings created successfully', [
                'psw_id' => $event->psw->id,
                'email' => $event->psw->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create PSW profile or notification settings', [
                'psw_id' => $event->psw->id,
                'email' => $event->psw->email,
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger retry mechanism if using queues
            throw $e;
        }
    }

    /**
     * Handle a job failure
     *
     * @param PswRegistered $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(PswRegistered $event, \Throwable $exception): void
    {
        Log::critical('PSW profile creation failed permanently', [
            'psw_id' => $event->psw->id,
            'email' => $event->psw->email,
            'error' => $exception->getMessage()
        ]);
    }
}