<?php

namespace App\Support\Contracts;

use App\Models\Event;

interface EVFUser
{
    /**
     * Get the name of the user.
     *
     * @return string
     */
    public function getAuthName();

    /**
     * Get the session ID name to user
     *
     * @return string
     */
    public function getAuthSessionName();

    /**
     * Get a list of roles for this user
     * 
     * @return string[]
     */
    public function getAuthRoles(Event $event): array;
}
