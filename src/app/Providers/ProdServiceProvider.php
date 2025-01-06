<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\Repositories\JourneyLogRepository;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\Repositories\JourneyLogLinkTypeRepository;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\Infrastructures\Repositories\SongRepository;

class ProdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, JourneyLogRepository::class);

        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, JourneyLogLinkTypeRepository::class);

        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);

        $this->auth();
    }

    public function boot(): void
    {
    }

    private function auth(): void
    {
        $this->app->bind(\Auth\UseCases\Login\LoginUseCaseInterface::class, \Auth\Application\Login\LoginInteractor::class);
    }
}
