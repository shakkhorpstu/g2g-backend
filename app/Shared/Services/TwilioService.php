<?php

namespace App\Shared\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Exception;

class TwilioService
{
    protected Client $client;
    protected string $fromNumber;

    public function __construct()
    {
        $accountSid = config('twilio.account_sid');
        $authToken = config('twilio.auth_token');
        $fromNumber = config('twilio.phone_number');

        if (!$accountSid || !$authToken || !$fromNumber) {
            throw new Exception('Twilio configuration is missing. Please check your .env file and ensure TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, and TWILIO_PHONE_NUMBER are set.');
        }

        $this->fromNumber = $fromNumber;
        $this->client = new Client($accountSid, $authToken);
    }

    /**
     * Send SMS message
     *
     * @param string $to Phone number in E.164 format (e.g., +1234567890)
     * @param string $message Message content
     * @return array
     * @throws Exception
     */
    public function sendSMS(string $to, string $message): array
    {
        try {
            $twilioMessage = $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );

            return [
                'success' => true,
                'message_sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
                'to' => $twilioMessage->to,
                'from' => $twilioMessage->from,
                'date_created' => $twilioMessage->dateCreated->format('Y-m-d H:i:s'),
            ];
        } catch (TwilioException $e) {
            throw new Exception('Twilio Error: ' . $e->getMessage());
        }
    }

    /**
     * Get message status
     *
     * @param string $messageSid Message SID from Twilio
     * @return array
     * @throws Exception
     */
    public function getMessageStatus(string $messageSid): array
    {
        try {
            $message = $this->client->messages($messageSid)->fetch();

            return [
                'sid' => $message->sid,
                'status' => $message->status,
                'to' => $message->to,
                'from' => $message->from,
                'body' => $message->body,
                'date_created' => $message->dateCreated->format('Y-m-d H:i:s'),
                'date_updated' => $message->dateUpdated->format('Y-m-d H:i:s'),
            ];
        } catch (TwilioException $e) {
            throw new Exception('Twilio Error: ' . $e->getMessage());
        }
    }
}
