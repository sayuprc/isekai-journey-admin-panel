<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\Application\Uuid\DummyUuidGenerator;
use App\Shared\Uuid\UuidGeneratorInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UuidGeneratorInterface::class, DummyUuidGenerator::class);
    }

    public function boot(): void
    {
        $url = config('app.url');
        assert(is_string($url));

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');
    }
}
