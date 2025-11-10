<?php

namespace Modules\Core\Events;

use Modules\Core\Models\Psw;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * PSW Registered Event
 * 
 * Fired when a new PSW is successfully registered.
 * This event allows other modules to react to PSW registration
 * without creating tight coupling between services.
 */
class PswRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The registered PSW instance
     *
     * @var Psw
     */
    public Psw $psw;

    /**
     * Additional registration data
     *
     * @var array
     */
    public array $registrationData;

    /**
     * Create a new event instance
     *
     * @param Psw $psw
     * @param array $registrationData
     */
    public function __construct(Psw $psw, array $registrationData = [])
    {
        $this->psw = $psw;
        $this->registrationData = $registrationData;
    }
}