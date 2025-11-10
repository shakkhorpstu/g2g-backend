<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'appointment_notification',
        'activity_email',
        'activity_sms',
        'activity_push',
        'promotional_email',
        'promotional_sms',
        'promotional_push',
    ];

    protected $casts = [
        'appointment_notification' => 'boolean',
        'activity_email' => 'boolean',
        'activity_sms' => 'boolean',
        'activity_push' => 'boolean',
        'promotional_email' => 'boolean',
        'promotional_sms' => 'boolean',
        'promotional_push' => 'boolean',
    ];

    /**
     * Get the owning notifiable model (User or Psw).
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get default notification settings
     */
    public static function getDefaults(): array
    {
        return [
            'appointment_notification' => true,
            'activity_email' => true,
            'activity_sms' => true,
            'activity_push' => true,
            'promotional_email' => true,
            'promotional_sms' => true,
            'promotional_push' => true,
        ];
    }

    /**
     * Create default notification settings for a notifiable model
     */
    public static function createDefaults($notifiable): self
    {
        return self::create(array_merge(
            self::getDefaults(),
            [
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable),
            ]
        ));
    }
}