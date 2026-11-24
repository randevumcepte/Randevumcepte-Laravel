<?php

namespace App\Providers;
use App\Auth\TipliTokenGuard;
use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();

        // Token tipi denetimi (varsayilan kapali): kapaliyken Passport'un kendi guard'i aynen kalir
        if (\App\Services\TokenTipi::acik()) $this->tipliTokenGuard();
    }

    /** PassportServiceProvider::registerGuard (4.0.3) ile ayni; yalniz guard sinifi farkli. */
    private function tipliTokenGuard()
    {
        Auth::extend('passport', function ($app, $name, array $config) {
            $guard = new RequestGuard(function ($request) use ($app, $config) {
                return (new TipliTokenGuard(
                    $app->make(ResourceServer::class),
                    Auth::createUserProvider($config['provider']),
                    $app->make(TokenRepository::class),
                    $app->make(ClientRepository::class),
                    $app->make('encrypter')
                ))->user($request);
            }, $app['request']);
            $app->refresh('request', $guard, 'setRequest');
            return $guard;
        });
    }
}
