<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $url = config()->string('app.url');

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');
    }
}
