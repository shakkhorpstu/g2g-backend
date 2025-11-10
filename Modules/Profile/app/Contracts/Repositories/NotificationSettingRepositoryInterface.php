<?php

namespace Modules\Profile\Contracts\Repositories;

use Modules\Profile\Models\NotificationSetting;

interface NotificationSettingRepositoryInterface
{
    /**
     * Find notification settings by notifiable entity
     *
     * @param string $notifiableType
     * @param int $notifiableId
     * @return NotificationSetting|null
     */
    public function findByNotifiable(string $notifiableType, int $notifiableId): ?NotificationSetting;

    /**
     * Create or update notification settings
     *
     * @param string $notifiableType
     * @param int $notifiableId
     * @param array $settings
     * @return NotificationSetting
     */
    public function createOrUpdate(string $notifiableType, int $notifiableId, array $settings): NotificationSetting;
}