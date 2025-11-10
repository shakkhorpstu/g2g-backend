<?php

namespace Modules\Profile\Repositories;

use Modules\Profile\Models\NotificationSetting;
use Modules\Profile\Contracts\Repositories\NotificationSettingRepositoryInterface;

class NotificationSettingRepository implements NotificationSettingRepositoryInterface
{

    /**
     * Find notification settings by notifiable entity
     *
     * @param string $notifiableType
     * @param int $notifiableId
     * @return NotificationSetting|null
     */
    public function findByNotifiable(string $notifiableType, int $notifiableId): ?NotificationSetting
    {
        return NotificationSetting::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->first();
    }

    /**
     * Create or update notification settings
     *
     * @param string $notifiableType
     * @param int $notifiableId
     * @param array $settings
     * @return NotificationSetting
     */
    public function createOrUpdate(string $notifiableType, int $notifiableId, array $settings): NotificationSetting
    {
        return NotificationSetting::updateOrCreate(
            [
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
            ],
            $settings
        );
    }
}