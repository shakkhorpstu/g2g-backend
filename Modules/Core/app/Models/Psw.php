<?php

namespace Modules\Core\Models;

use App\Shared\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Cashier\Billable;

class Psw extends Authenticatable
{
    /** @use HasFactory<\Modules\Core\Database\Factories\PswFactory> */
    use HasApiTokens, HasFactory, Notifiable, Billable, HasAddresses;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psws';

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
        'last_login_at',
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
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Modules\Core\Database\Factories\PswFactory::new();
    }

    /**
     * Get the profile associated with the PSW.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(\Modules\Profile\Models\PswProfile::class, 'psw_id');
    }

    /**
     * Get the notification setting for the PSW.
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
     * Get the PSW's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}