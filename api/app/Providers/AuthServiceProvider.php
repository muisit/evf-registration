<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\Event;
use App\Models\WPUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Support\SessionGuard;
use App\Support\DeviceGuard;
use App\Support\UserProvider;
use App\Support\Contracts\EVFUser;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // register this here, because the SessionGuard will register another provider that needs
        // to be booted. If we register in the boot() method, the new provider is not booted properly
        $this->app['auth']->extend('evf', function ($app, $name, $config) {
            $guard = new SessionGuard($name, new UserProvider(), $app['session.store']);
            $guard->setCookieJar($this->app['cookie']);
            $guard->setDispatcher($this->app['events']);
            $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
    
            return $guard;
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        Gate::policy(\App\Models\Event::class, \App\Models\Policies\Event::class);
        Gate::policy(\App\Models\Fencer::class, \App\Models\Policies\Fencer::class);
        Gate::policy(\App\Models\Country::class, \App\Models\Policies\Country::class);
        Gate::policy(\App\Models\Registration::class, \App\Models\Policies\Registration::class);
        Gate::policy(\App\Models\Accreditation::class, \App\Models\Policies\Accreditation::class);
        Gate::policy(\App\Models\AccreditationDocument::class, \App\Models\Policies\AccreditationDocument::class);
        Gate::policy(\App\Models\AccreditationTemplate::class, \App\Models\Policies\AccreditationTemplate::class);
        Gate::policy(\App\Models\AccreditationUser::class, \App\Models\Policies\AccreditationUser::class);
        Gate::policy(\App\Models\WPUser::class, \App\Models\Policies\WPUser::class);
        Gate::policy(\App\Models\Follow::class, \App\Models\Policies\Follow::class);

        // subscribe authentications. Each channel is linked to a front-end functionality
        // named after the role, so only users with access to that functionality can
        // subscribe
        // Who can post is not determined by this authentication, but by the system generating
        // specific events for specific channels.
        Broadcast::channel('accredit.{eventId}', function (EVFUser $user, int $eventId) {
            return $user->hasRole('code') && $user->hasRole("accreditation:" . $eventId);
        });
        Broadcast::channel('checkin.{eventId}', function (EVFUser $user, int $eventId) {
            \Log::debug("checkin, user has roles " . json_encode($user->getAuthRoles()));
            return $user->hasRole('code') && $user->hasRole(["checkin:" . $eventId]);
        });
        Broadcast::channel('checkout.{eventId}', function (EVFUser $user, int $eventId) {
            \Log::debug("checkout, user has roles " . json_encode($user->getAuthRoles()));
            return $user->hasRole('code') && $user->hasRole(["checkout:" . $eventId]);
        });
        Broadcast::channel('dt.{eventId}', function (EVFUser $user, int $eventId) {
            return $user->hasRole('code') && $user->hasRole(["dt:" . $eventId]);
        });

        Auth::viaRequest('device', function (Request $request) {
            $device = Device::where('uuid', (string) $request->bearerToken())->first();
            $user = $device?->user;
            $request->merge(['device' => $device]);
            return $user;
        });

        Auth::viaRequest('wp', function (Request $request) {
            $option = DB::table(env('WPDBPREFIX', 'wp_') . "options")
                ->where("option_name", "evf_internal_key")
                ->where("option_value", (string) $request->bearerToken())
                ->first();

            if (!empty($option)) {
                $userid = DB::table(env('WPDBPREFIX', 'wp_') . "options")
                    ->where("option_name", "evf_internal_user")
                    ->first();
                if (!empty($userid)) {
                    return WPUser::find($userid->option_value);
                }
            }
            return null;
        });
    }
}
