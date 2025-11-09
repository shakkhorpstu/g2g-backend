<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration for Core Module
    |--------------------------------------------------------------------------
    |
    | This configuration file defines authentication guards, providers, and
    | password reset settings that work with the Core module's User model.
    | All authentication logic is implemented in Modules/Core/.
    |
    | This file remains at the root level as Laravel expects it here for
    | framework integration, but it references Core module components.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Guards define how users are authenticated for each request. Our setup:
    | - 'web': Session-based auth for web interface (uses Core User model)
    | - 'api': Passport JWT auth for API endpoints (uses Core User model) 
    | - 'admin-api': Passport JWT auth for admin endpoints (uses Core User model with role check)
    |
    | All guards use the Core module's User model but with different drivers
    | and providers based on the authentication method required.
    |
    | Authentication logic is implemented in Modules/Core/Services/AuthService
    |
    | Supported drivers: "session", "passport"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
        'admin-api' => [
            'driver' => 'passport',
            'provider' => 'admins',
        ],
        'psw-api' => [
            'driver' => 'passport',
            'provider' => 'psws',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers  
    |--------------------------------------------------------------------------
    |
    | User providers define how users are retrieved from storage. All providers
    | now use the Core module's User model (Modules\Core\Models\User).
    |
    | - 'users': Standard user provider for regular authentication
    | - 'admins': Admin provider (same model, but intended for admin guards)
    |
    | Both providers use the same Core User model. Role-based access control
    | is handled in the AuthService, not at the provider level.
    |
    | The AUTH_MODEL environment variable can override the default model.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', Modules\Core\Models\User::class),
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => Modules\Core\Models\Admin::class, // Use Core Admin model
        ],
        'psws' => [
            'driver' => 'eloquent',
            'model' => Modules\Core\Models\Psw::class, // Use Core PSW model
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'psws' => [
            'provider' => 'psws',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
