<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OtpSmsNotification extends Notification
{
    use Queueable;

    public function __construct(public string $otpCode)
    {
    }

    public function via($notifiable): array
    {
        // Log to file instead of database until SMS service is configured
        // To enable SMS: add 'nexmo' or 'twilio' channel here
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your verification code is: ' . $this->otpCode,
            'code' => $this->otpCode,
            'expires_in_minutes' => config('app.reset_otp_life', 10),
        ];
    }

    // Handle notification by logging SMS details
    public function handle()
    {
        Log::info('SMS OTP Notification', [
            'code' => $this->otpCode,
            'message' => 'Your verification code is: ' . $this->otpCode,
            'expires_in_minutes' => config('app.reset_otp_life', 10),
        ]);
    }

    // Example for Twilio/Nexmo - uncomment and configure when SMS service is ready
    // public function toNexmo($notifiable)
    // {
    //     return (new NexmoMessage)
    //         ->content('Your verification code is: ' . $this->otpCode);
    // }
}
