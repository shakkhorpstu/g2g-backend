<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OtpVerification extends Model
{
    protected $fillable = [
        'otpable_type',
        'otpable_id',
        'identifier',
        'otp_code',
        'type',
        'status',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer'
    ];

    /**
     * Polymorphic relationship to User/PSW/Admin
     */
    public function otpable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if max attempts reached
     */
    public function hasMaxAttemptsReached(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Mark as verified
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'updated_at' => now()
        ]);
    }
}