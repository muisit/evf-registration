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
        if (!empty($id) && $this->user = $this->provider->retrieveWPUserById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }
        $id = $this->session->get('accreditationuser');
        if (empty($user) && !empty($id) && $this->user = $this->provider->retrieveAccreditationUserById($id)) {
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

    /**
     * Remove the user data from the session and cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        if ($this->user && object_implements($this->user, EVFUser::class)) {
            $this->session->remove($this->user->getAuthSessionName());
        }
        parent::clearUserDataFromStorage();
    }
}
