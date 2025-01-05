<?php

declare(strict_types=1);

namespace App\Providers;

use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLog\Infrastructures\Repositories\JourneyLogRepository;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Features\JourneyLogLinkType\Infrastructures\Repositories\JourneyLogLinkTypeRepository;
use App\Features\Song\Domain\Repositories\SongRepositoryInterface;
use App\Features\Song\Infrastructures\Repositories\SongRepository;
use Illuminate\Support\ServiceProvider;

class ProdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, JourneyLogRepository::class);

        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, JourneyLogLinkTypeRepository::class);

        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
    }

    public function boot(): void
    {
    }
}
