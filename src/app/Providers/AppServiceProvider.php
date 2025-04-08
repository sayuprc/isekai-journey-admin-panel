<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\EnvProviders\MockServiceProvider;
use App\Providers\EnvProviders\ProdServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Support\Application\Mapper\Mapper;
use Support\Mapper\MapperInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
