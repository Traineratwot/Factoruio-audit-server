<?php

namespace App\Modules\RequestLog;

use App\Modules\RequestLog\Helpers\RequestLogHelper;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class RequestLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/requestlog.php', 'requestlog');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/requestlog.php' => config_path('requestlog.php'),
            ], 'requestlog-config');
        }

        $this->registerHttpMacro();
    }

    private function registerHttpMacro(): void
    {
        Http::macro('withLog', function (): PendingRequest {
            return Http::setHandler(RequestLogHelper::createHandlerStack());
        });
    }
}
