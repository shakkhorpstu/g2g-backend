<?php

namespace Modules\Core\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Shared\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Modules\Core\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Billable, HasAddresses;

    /**
     * User status constants
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_SUSPENDED = 3;
    public const STATUS_DELETED = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'gender',
        'is_verified',
        'status',
        'last_login_at',
        'email_verified_at',
        'meta'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'status' => 'integer',
            'meta' => 'array'
        ];
    }

    /**
     * Get status text representation
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_DELETED => 'Deleted',
            default => 'Unknown',
        };
    }

    /**
     * Check if user is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's notification settings.
     */
    public function notificationSetting(): MorphOne
    {
        return $this->morphOne(\Modules\Profile\Models\NotificationSetting::class, 'notifiable');
    }

    public function otpVerification(): MorphMany
    {
        return $this->morphMany(\Modules\Core\Models\OtpVerification::class, 'otpable');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Core\Database\Factories\UserFactory::new();
    }
}