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
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->extend('evf', function ($app, $name, $config) {
            $guard = new SessionGuard($name, new UserProvider(), $app['session.store']);
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($this->app['cookie']);
            }
    
            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }
    
            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }
    
            return $guard;
        });
    }
}
