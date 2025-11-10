<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Profile\Services\NotificationSettingService;
use Modules\Profile\Http\Requests\UpdateNotificationSettingsRequest;

class NotificationController extends ApiController
{
    protected NotificationSettingService $notificationSettingService;

    public function __construct(NotificationSettingService $notificationSettingService)
    {
        parent::__construct();
        $this->notificationSettingService = $notificationSettingService;
    }

    /**
     * Get notification configuration for authenticated user
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->executeService(
            fn() => $this->notificationSettingService->getNotificationSettings()
        );
    }

    /**
     * Update notification settings for authenticated user
     *
     * @param UpdateNotificationSettingsRequest $request
     * @return JsonResponse
     */
    public function update(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        return $this->executeService(
            fn() => $this->notificationSettingService->saveNotificationSettings($request->getSanitizedData())
        );
    }
}