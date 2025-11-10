<?php

namespace Modules\Profile\Services;

use Modules\Profile\Models\NotificationSetting;
use Modules\Profile\Contracts\Repositories\NotificationSettingRepositoryInterface;
use App\Shared\Services\BaseService;

class NotificationSettingService extends BaseService
{
    /**
     * Notification settings repository instance
     *
     * @var NotificationSettingRepositoryInterface
     */
    protected NotificationSettingRepositoryInterface $notificationSettingRepository;

    /**
     * NotificationSettingService constructor
     *
     * @param NotificationSettingRepositoryInterface $notificationSettingRepository
     */
    public function __construct(NotificationSettingRepositoryInterface $notificationSettingRepository)
    {
        $this->notificationSettingRepository = $notificationSettingRepository;
    }

    /**
     * Get notification settings for authenticated user
     *
     * @return array
     */
    public function getNotificationSettings(): array
    {
        $user = $this->getAuthenticatedUserOrFail();

        $notifiableType = get_class($user);
        $notifiableId = $user->id;

        $settings = $this->notificationSettingRepository->findByNotifiable($notifiableType, $notifiableId);
        
        if (!$settings) {
            // Return default settings if none exist
            $defaultSettings = NotificationSetting::getDefaults();
            return $this->success($this->getResponseFormat((object) $defaultSettings), 'Default notification settings retrieved');
        }

        return $this->success($this->getResponseFormat($settings), 'Notification settings retrieved successfully');
    }

    /**
     * Save or update notification settings for authenticated user
     *
     * @param array $data
     * @return array
     */
    public function saveNotificationSettings(array $data): array
    {
        return $this->executeWithTransaction(function () use ($data) {
            $user = $this->getAuthenticatedUserOrFail();

            $notifiableType = get_class($user);
            $notifiableId = $user->id;

            $validatedSettings = $this->validateSettings($data);
            
            $updatedSettings = $this->notificationSettingRepository->createOrUpdate($notifiableType, $notifiableId, $validatedSettings);

            return $this->success($this->getResponseFormat($updatedSettings), 'Notification settings updated successfully');
        });
    }

    protected function validateSettings(array $settings): array
    {
        $allowedKeys = [
            'appointment_notification',
            'activity_email',
            'activity_sms', 
            'activity_push',
            'promotional_email',
            'promotional_sms',
            'promotional_push'
        ];

        $validated = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $settings)) {
                $validated[$key] = (bool) $settings[$key];
            }
        }

        return $validated;
    }

    private function getResponseFormat($settings): array
    {
        return [
            'appointment_notification' => $settings->appointment_notification,
            'activity_email' => $settings->activity_email,
            'activity_sms' => $settings->activity_sms,
            'activity_push' => $settings->activity_push,
            'promotional_email' => $settings->promotional_email,
            'promotional_sms' => $settings->promotional_sms,
            'promotional_push' => $settings->promotional_push,
        ];
    }
}