<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .otp-code {
            background-color: #f0f9ff;
            border: 2px dashed #2563eb;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
            border-radius: 8px;
        }
        .otp-number {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <h1>
                @if($type === 'account_verification')
                    Account Verification
                @elseif($type === 'password_reset')
                    Password Reset
                @elseif($type === 'login_verification')
                    Login Verification
                @else
                    Verification Code
                @endif
            </h1>
        </div>

        <p>Hello,</p>

        @if($type === 'account_verification')
            <p>Thank you for registering with {{ config('app.name') }}! To complete your account verification, please use the following OTP code:</p>
        @elseif($type === 'password_reset')
            <p>We received a request to reset your password. Please use the following OTP code to proceed:</p>
        @elseif($type === 'login_verification')
            <p>We detected a login attempt to your account. Please use the following OTP code to verify it's you:</p>
        @else
            <p>Please use the following OTP code for verification:</p>
        @endif

        <div class="otp-code">
            <p><strong>Your Verification Code:</strong></p>
            <div class="otp-number">{{ $otpCode }}</div>
            <p>This code will expire on <strong>{{ $expiresAt }}</strong></p>
        </div>

        <div class="warning">
            <p><strong>⚠️ Security Notice:</strong></p>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This code is valid for 5 minutes only</li>
                <li>Do not share this code with anyone</li>
                <li>If you didn't request this code, please ignore this email</li>
                <li>Contact support if you have any concerns</li>
            </ul>
        </div>

        @if($type === 'account_verification')
            <p>Once verified, you'll have full access to your {{ config('app.name') }} account.</p>
        @endif

        <p>Best regards,<br>
        The {{ config('app.name') }} Team</p>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>