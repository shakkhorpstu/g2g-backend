<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpEmailNotification extends Notification
{
    use Queueable;

    public function __construct(public string $otpCode)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Verification Code')
            ->line('Your OTP verification code is: **' . $this->otpCode . '**')
            ->line('This code will expire in ' . config('app.reset_otp_life', 10) . ' minutes.')
            ->line('If you did not request this code, please ignore this email.');
    }
}
