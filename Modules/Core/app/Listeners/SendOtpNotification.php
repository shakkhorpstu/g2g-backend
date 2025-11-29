<?php

namespace Modules\Core\Listeners;

use Modules\Core\Events\OtpSent;
use Modules\Core\Mail\OtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OtpSent $event): void
    {
        $otpVerification = $event->otpVerification;
        
        try {
            // Check if identifier is email or phone number
            if (filter_var($otpVerification->identifier, FILTER_VALIDATE_EMAIL)) {
                // Identifier is an email; send directly
                Mail::to($otpVerification->identifier)->send(new OtpMail($otpVerification));
                Log::info('OTP email sent (identifier email)', [
                    'identifier' => $otpVerification->identifier,
                    'type' => $otpVerification->type,
                    'otpable_type' => $otpVerification->otpable_type,
                    'otpable_id' => $otpVerification->otpable_id
                ]);
            } else {
                // Identifier assumed phone; send email to otpable's registered email as backup
                $otpable = $otpVerification->otpable;
                if ($otpable && $otpable->email) {
                    Mail::to($otpable->email)->send(new OtpMail($otpVerification));
                    Log::info('OTP email sent (phone identifier, user email fallback)', [
                        'phone' => $otpVerification->identifier,
                        'email' => $otpable->email,
                        'type' => $otpVerification->type,
                        'otpable_type' => $otpVerification->otpable_type,
                        'otpable_id' => $otpVerification->otpable_id
                    ]);
                } else {
                    Log::warning('No email found for otpable when phone OTP requested', [
                        'phone' => $otpVerification->identifier,
                        'otpable_type' => $otpVerification->otpable_type,
                        'otpable_id' => $otpVerification->otpable_id
                    ]);
                }
                // Log placeholder for future SMS integration
                Log::info('SMS OTP placeholder (not implemented)', [
                    'phone' => $otpVerification->identifier,
                    'type' => $otpVerification->type,
                    'otp_id' => $otpVerification->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send OTP notification', [
                'identifier' => $otpVerification->identifier,
                'type' => $otpVerification->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw exception to mark job as failed if using queues
            throw $e;
        }
    }
}