<?php

namespace App\Providers;

use App\Models\WPUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Support\SessionGuard;
use App\Support\UserProvider;

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
        Gate::policy(\App\Models\WPUser::class, \App\Models\Policies\WPUser::class);
    }
}
