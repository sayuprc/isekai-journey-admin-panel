<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\OpenApiConfig;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OpenApiConfig::class,
            fn (): OpenApiConfig => new OpenApiConfig(config()->string('openapi.path')),
        );
    }

    public function boot(): void
    {
        $url = config()->string('app.url');

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');
    }
}
