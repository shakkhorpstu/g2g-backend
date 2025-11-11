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
                // Send email
                Mail::to($otpVerification->identifier)->send(new OtpMail($otpVerification));
                
                Log::info('OTP email sent successfully', [
                    'identifier' => $otpVerification->identifier,
                    'type' => $otpVerification->type,
                    'otpable_type' => $otpVerification->otpable_type,
                    'otpable_id' => $otpVerification->otpable_id
                ]);
            } else {
                // For phone numbers - SMS functionality (to be implemented later)
                Log::info('SMS OTP sending not implemented yet', [
                    'identifier' => $otpVerification->identifier,
                    'type' => $otpVerification->type
                ]);
                
                // TODO: Implement SMS sending
                // SMS::send($otpVerification->identifier, "Your OTP: " . decrypt($otpVerification->otp_code));
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