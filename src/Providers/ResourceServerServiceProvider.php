<?php

namespace Codewiser\ResourceServer\Providers;

use Codewiser\ResourceServer\Console\Commands\ShowAccessToken;
use Codewiser\ResourceServer\Services\ResourceServerService;
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
        $this->app->singleton('ResourceServer', function() {
            return new ResourceServerService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/resource_server.php' => config_path('resource_server.php')
        ], 'resource_server-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ShowAccessToken::class
            ]);
        }
    }
}
