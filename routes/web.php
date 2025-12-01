<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Debug route: send a synchronous test email to verify server mail configuration.
 * Usage: /debug/send-test-mail?to=you@example.com&subject=Test
 */
Route::get('/debug/send-test-mail', function (Request $request) {
    $to = $request->query('to', null);
    $subject = $request->query('subject', 'Test email from application');

    if (! $to) {
        return response()->json(['status' => 'error', 'message' => 'Provide ?to=you@example.com'], 400);
    }

    if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return response()->json(['status' => 'error', 'message' => 'Invalid email address'], 400);
    }

    try {
        $body = "This is a test email sent at " . now()->toDateTimeString() . " to verify mail configuration.";

        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });

        Log::info('Debug test email sent', ['to' => $to, 'subject' => $subject]);

        return response()->json(['status' => 'success', 'message' => 'Test email sent', 'to' => $to]);
    } catch (\Exception $e) {
        Log::error('Failed to send debug test email', ['to' => $to, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

        return response()->json(['status' => 'error', 'message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
    }
});
