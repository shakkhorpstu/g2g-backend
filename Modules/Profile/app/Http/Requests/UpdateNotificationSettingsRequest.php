<?php

namespace Modules\Profile\Http\Requests;

class UpdateNotificationSettingsRequest extends BaseProfileRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_notification' => 'sometimes|boolean',
            'activity_email' => 'sometimes|boolean',
            'activity_sms' => 'sometimes|boolean',
            'activity_push' => 'sometimes|boolean',
            'promotional_email' => 'sometimes|boolean',
            'promotional_sms' => 'sometimes|boolean',
            'promotional_push' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'appointment_notification.boolean' => 'Appointment notification must be true or false',
            'activity_email.boolean' => 'Activity email notification must be true or false',
            'activity_sms.boolean' => 'Activity SMS notification must be true or false',
            'activity_push.boolean' => 'Activity push notification must be true or false',
            'promotional_email.boolean' => 'Promotional email notification must be true or false',
            'promotional_sms.boolean' => 'Promotional SMS notification must be true or false',
            'promotional_push.boolean' => 'Promotional push notification must be true or false',
        ];
    }

    /**
     * Get sanitized data for the request.
     *
     * @return array
     */
    public function getSanitizedData(): array
    {
        return $this->only([
            'appointment_notification',
            'activity_email',
            'activity_sms',
            'activity_push',
            'promotional_email',
            'promotional_sms',
            'promotional_push',
        ]);
    }
}