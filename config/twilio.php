<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twilio Account SID
    |--------------------------------------------------------------------------
    |
    | Your Twilio Account SID from https://www.twilio.com/console
    |
    */
    'account_sid' => env('TWILIO_ACCOUNT_SID'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Auth Token
    |--------------------------------------------------------------------------
    |
    | Your Twilio Auth Token from https://www.twilio.com/console
    |
    */
    'auth_token' => env('TWILIO_AUTH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Phone Number
    |--------------------------------------------------------------------------
    |
    | Your Twilio phone number in E.164 format (e.g., +1234567890)
    |
    */
    'phone_number' => env('TWILIO_PHONE_NUMBER'),
];
