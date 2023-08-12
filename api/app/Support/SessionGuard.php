<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\SessionGuard as SessionGuardBase;
use App\Models\WPUser;
use App\Support\Traits\EVFUser;

class SessionGuard extends SessionGuardBase
{
    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get('wpuser');

        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        if (! is_null($id) && $this->user = $this->provider->retrieveWPUserById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }
        return $this->user;
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(AuthenticatableContract $user, $remember = false)
    {
        if (object_implements($user, EVFUser::class)) {
            $this->session->put($user->getAuthSessionName(), $user->getAuthIdentifier());
        }
        $this->session->migrate(true);
        $this->fireLoginEvent($user, $remember);
        $this->setUser($user);
    }
}
