<?php

namespace Metrogistics\AzureSocialite;

use Illuminate\Support\Facades\Auth;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Metrogistics\AzureSocialite\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        // $this->app->bind('azure-user', function(){
        //     return new AzureUser(
        //         session('azure_user')
        //     );
        // });
    }

    public function boot()
    {
        // Auth::extend('azure', function(){
        //     dd('test');
        //     return new Authenticate();
        // });

        $this->publishes([
            __DIR__ . '/config/oauth-azure.php' => config_path('oauth-azure.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/oauth-azure.php', 'oauth-azure'
        );

        $this->app['Laravel\Socialite\Contracts\Factory']->extend('oauth-azure', function($app){
            return $app['Laravel\Socialite\Contracts\Factory']->buildProvider(
                'Metrogistics\AzureSocialite\AzureOauthProvider',
                config('oauth-azure.credentials')
            );
        });

        $this->app['router']->group(['middleware' => config('oauth-azure.routes.middleware')], function($router){
            $router->get(config('oauth-azure.routes.login'), 'Metrogistics\AzureSocialite\AuthController@redirectToOauthProvider')->name('oauth.login');
            $router->get(config('oauth-azure.routes.callback'), 'Metrogistics\AzureSocialite\AuthController@handleOauthResponse')->name('oauth.redirect');
        });
    }
}
