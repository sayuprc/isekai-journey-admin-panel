<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\EnvProviders\MockServiceProvider;
use App\Providers\EnvProviders\ProdServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Shared\Application\Mapper\Mapper;
use Shared\Application\Uuid\DummyUuidGenerator;
use Shared\Mapper\MapperInterface;
use Shared\Uuid\UuidGeneratorInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UuidGeneratorInterface::class, DummyUuidGenerator::class);

        $this->app->bind(MapperInterface::class, Mapper::class);

        $this->provider()->register();
    }

    public function boot(): void
    {
        $url = config('app.url');
        assert(is_string($url));

        URL::forceScheme(str_starts_with($url, 'https') ? 'https' : 'http');
    }

    private function provider(): ServiceProvider
    {
        return config('app.env') === 'mock'
            ? new MockServiceProvider($this->app)
            : new ProdServiceProvider($this->app);
    }
}
