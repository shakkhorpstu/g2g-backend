<?php

namespace Modules\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\OtpVerification;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $type;
    public string $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct(public OtpVerification $otpVerification)
    {
        $this->otpCode = decrypt($this->otpVerification->otp_code);
        $this->type = $this->otpVerification->type;
        $this->expiresAt = $this->otpVerification->expires_at->format('M d, Y H:i A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->type) {
            'account_verification' => 'Verify Your Account - OTP Code',
            'password_reset' => 'Password Reset - OTP Code',
            'login_verification' => 'Login Verification - OTP Code',
            default => 'Verification Code - OTP'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'core::emails.otp',
            with: [
                'otpCode' => $this->otpCode,
                'type' => $this->type,
                'expiresAt' => $this->expiresAt,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}