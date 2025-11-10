<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Notification Settings Model
 * 
 * Handles notification preferences for both Users and PSWs
 * using polymorphic relationship
 */
class NotificationSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
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
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return [
            'appointment_notification' => true,
            'activity_email' => true,
            'activity_sms' => true,
            'activity_push' => true,
            'promotional_email' => false,
            'promotional_sms' => false,
            'promotional_push' => true,
        ];
    }

    /**
     * Create default settings for a notifiable entity
     *
     * @param mixed $notifiable
     * @return NotificationSetting
     */
    public static function createDefaults($notifiable): NotificationSetting
    {
        return self::create([
            'notifiable_id' => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
            ...self::getDefaults()
        ]);
    }
}