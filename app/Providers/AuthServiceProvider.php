<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //

        //define a custom guard
        Auth::extend('tokenDriver', function ($app, $name, array $config) {
			$request = app('request');
			$userProvider = new \App\Auth\TokenUserProvider($request, $config);

            // Return an instance of Illuminate\Contracts\Auth\Guard...
			return new \App\Auth\TokenGuard($name, $userProvider, $request, $config);
		});
    }
}
