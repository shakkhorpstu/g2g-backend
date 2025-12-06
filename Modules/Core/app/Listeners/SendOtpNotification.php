<?php

namespace Modules\Core\Listeners;

use Modules\Core\Events\OtpSent;
use Modules\Core\Mail\OtpMail;
use App\Shared\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOtpNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected TwilioService $twilioService)
    {
    }

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
                // Identifier is phone number; send SMS via Twilio
                $otpCode = decrypt($otpVerification->otp_code);
                Log::info('OTP SMS sent via Twilio', [
                    'email' => $otpVerification->otpable->email,
                    'type' => $otpVerification->type,
                    'otpable_type' => $otpVerification->otpable_type,
                    'otpable_id' => $otpVerification->otpable_id
                ]);

                // here send mail also
                Mail::to($otpVerification->otpable->email)->send(new OtpMail($otpVerification));

                $message = $this->buildSmsMessage($otpCode, $otpVerification->type);
                
                try {
                    $result = $this->twilioService->sendSMS($otpVerification->identifier, $message);
                    
                    Log::info('OTP SMS sent via Twilio', [
                        'phone' => $otpVerification->identifier,
                        'type' => $otpVerification->type,
                        'message_sid' => $result['message_sid'],
                        'status' => $result['status'],
                        'otpable_type' => $otpVerification->otpable_type,
                        'otpable_id' => $otpVerification->otpable_id
                    ]);
                } catch (\Exception $smsException) {
                    Log::error('Failed to send SMS via Twilio', [
                        'phone' => $otpVerification->identifier,
                        'type' => $otpVerification->type,
                        'error' => $smsException->getMessage()
                    ]);
                    
                    // Fallback: try sending email to user if available
                    $otpable = $otpVerification->otpable;
                    if ($otpable && $otpable->email) {
                        Mail::to($otpable->email)->send(new OtpMail($otpVerification));
                        Log::info('OTP email sent as SMS fallback', [
                            'phone' => $otpVerification->identifier,
                            'email' => $otpable->email,
                            'type' => $otpVerification->type
                        ]);
                    }
                    
                    // Re-throw to mark job as failed
                    throw $smsException;
                }
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

    /**
     * Build SMS message content based on OTP type
     *
     * @param string $otpCode
     * @param string $type
     * @return string
     */
    protected function buildSmsMessage(string $otpCode, string $type): string
    {
        $appName = config('app.name', 'App');
        
        return match($type) {
            'account_verification' => "{$appName}: Your verification code is {$otpCode}. Valid for 5 minutes.",
            'password_reset' => "{$appName}: Your password reset code is {$otpCode}. Valid for 5 minutes.",
            'email_update', 'phone_update' => "{$appName}: Your contact update code is {$otpCode}. Valid for 5 minutes.",
            default => "{$appName}: Your verification code is {$otpCode}. Valid for 5 minutes."
        };
    }
}
