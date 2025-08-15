<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Support\Infrastructures\Config\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $url = $this->app->make(Config::class)->getString('app.url');

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');
    }
}
