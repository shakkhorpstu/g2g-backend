<?php

namespace Modules\Core\Listeners;

use Modules\Core\Events\OtpVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogOtpVerification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OtpVerified $event): void
    {
        $otpVerification = $event->otpVerification;
        
        Log::info('OTP verified successfully', [
            'identifier' => $otpVerification->identifier,
            'type' => $otpVerification->type,
            'otpable_type' => $otpVerification->otpable_type,
            'otpable_id' => $otpVerification->otpable_id,
            'verified_at' => $otpVerification->verified_at
        ]);
    }
}