<?php

namespace Codewiser\ResourceServer\Providers;

use Codewiser\ResourceServer\Console\Commands\ShowAccessToken;
use Codewiser\ResourceServer\Services\OAuthClientService;
use Codewiser\ResourceServer\Services\ResourceServerService;
use Codewiser\UAC\ResourceServerContext;
use Illuminate\Support\ServiceProvider;

class ResourceServerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ResourceServer', function () {
            return new ResourceServerService(
                $this->getClientId(),
                $this->getClientSecret(),
                $this->getDefaultScope(),
                $this->getAuthorizationUrl(),
                $this->getGrantUrl(),
                $this->getResourceOwnerUrl(),
                $this->getIntrospectUrl()
            );
        });
        $this->app->singleton('OAuthClient', function () {
            return new OAuthClientService(
                $this->getClientId(),
                $this->getClientSecret(),
                $this->getDefaultScope(),
                $this->getAuthorizationUrl(),
                $this->getGrantUrl(),
                $this->getResourceOwnerUrl(),
                $this->getRedirectUri()
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/oauth.php');

        $this->publishes([
            __DIR__ . '/../../config/resource_server.php' => config_path('resource_server.php')
        ], 'resource_server-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ShowAccessToken::class
            ]);
        }
    }

    private function getClientId()
    {
        return config('resource_server.client_id');
    }

    private function getClientSecret()
    {
        return config('resource_server.client_secret');
    }

    private function getDefaultScope()
    {
        return config('resource_server.scopes');
    }

    private function getAuthorizationUrl()
    {
        return $this->buildUrl(
            config('resource_server.oauth_server'),
            config('resource_server.authorize_endpoint')
        );
    }

    private function getGrantUrl()
    {
        return $this->buildUrl(
            config('resource_server.oauth_server'),
            config('resource_server.token_endpoint')
        );
    }

    private function getResourceOwnerUrl()
    {
        return $this->buildUrl(
            config('resource_server.oauth_server'),
            config('resource_server.resource_owner_endpoint')
        );
    }

    private function getIntrospectUrl()
    {
        return $this->buildUrl(
            config('resource_server.oauth_server'),
            config('resource_server.introspection_endpoint')
        );
    }

    private function getRedirectUri()
    {
        return $this->buildUrl(
            env('APP_URL'),
            config('resource_server.redirect_uri')
        );
    }

    private function buildUrl($server, $uri)
    {
        if (parse_url($uri, PHP_URL_HOST)) {
            // Full URL in config
            return $uri;
        } else {
            // Partial URI in config
            $server = rtrim($server, '/');
            $path = ltrim($uri, '/');
            return $server . '/' . $path;
        }
    }
}
