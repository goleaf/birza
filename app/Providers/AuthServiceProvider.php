<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Users\Seller;
use App\Models\Users\Buyer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Explicitly register the authentication providers
        $this->app['auth']->provider('sellers', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider(
                $app['hash'],
                Seller::class
            );
        });

        $this->app['auth']->provider('buyers', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider(
                $app['hash'],
                Buyer::class
            );
        });
    }
}
