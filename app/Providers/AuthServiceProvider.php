<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend the admin guard to use a custom session key
        Auth::extend('admin_session', function ($app, $name, array $config) {
            // The guard name ($name = 'admin') automatically differentiates the session key
            // Laravel will use 'login_admin_{id}' for admin and 'login_web_{id}' for web
            $guard = new \Illuminate\Auth\SessionGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
                $app['request']
                );

            // Set the cookie jar to prevent "Cookie jar has not been set" error
            $guard->setCookieJar($app['cookie']);

            return $guard;
        });
    }
}