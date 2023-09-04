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
    public function getAuthRoles(?Event $event = null): array;

    /**
     * Determine if a user has an authorization role
     * @return bool
     */
    public function hasRole(string | array $role): bool;

    /**
     * Find roles that match a certain pattern
     * @return array list of matched roles
     */
    public function rolesLike(string $rolePart): array;
}
