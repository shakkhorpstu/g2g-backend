<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\OtpVerification;

class OtpSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OtpVerification $otpVerification
    ) {}
}