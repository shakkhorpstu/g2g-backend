<?php

namespace Modules\Core\Events;

use Modules\Core\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * User Registered Event
 * 
 * Fired when a new user is successfully registered.
 * This event allows other modules to react to user registration
 * without creating tight coupling between services.
 */
class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The registered user instance
     *
     * @var User
     */
    public User $user;

    /**
     * Additional registration data
     *
     * @var array
     */
    public array $registrationData;

    /**
     * Create a new event instance
     *
     * @param User $user
     * @param array $registrationData
     */
    public function __construct(User $user, array $registrationData = [])
    {
        $this->user = $user;
        $this->registrationData = $registrationData;
    }
}