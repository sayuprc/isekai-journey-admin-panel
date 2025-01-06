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

        $this->journeyLog();

        $this->journeyLogLinkType();

        $this->song();
    }

    public function boot(): void
    {
    }

    private function auth(): void
    {
        $this->app->bind(\Auth\UseCases\Login\LoginUseCaseInterface::class, \Auth\Application\Login\LoginInteractor::class);
    }

    private function journeyLog(): void
    {
        $this->app->bind(\JourneyLog\UseCases\List\ListUseCaseInterface::class, \JourneyLog\Application\List\ListInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Get\GetUseCaseInterface::class, \JourneyLog\Application\Get\GetInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Create\CreateUseCaseInterface::class, \JourneyLog\Application\Create\CreateInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Edit\EditUseCaseInterface::class, \JourneyLog\Application\Edit\EditInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Delete\DeleteUseCaseInterface::class, \JourneyLog\Application\Delete\DeleteInteractor::class);
    }

    private function journeyLogLinkType(): void
    {
        $this->app->bind(\JourneyLogLinkType\UseCases\List\ListUseCaseInterface::class, \JourneyLogLinkType\Application\List\ListInteractor::class);
        $this->app->bind(\JourneyLogLinkType\UseCases\Get\GetUseCaseInterface::class, \JourneyLogLinkType\Application\Get\GetInteractor::class);
        $this->app->bind(\JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface::class, \JourneyLogLinkType\Application\Create\CreateInteractor::class);
        $this->app->bind(\JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface::class, \JourneyLogLinkType\Application\Edit\EditInteractor::class);
        $this->app->bind(\JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface::class, \JourneyLogLinkType\Application\Delete\DeleteInteractor::class);
    }

    private function song(): void
    {
        $this->app->bind(\Song\UseCases\List\ListUseCaseInterface::class, \Song\Application\List\ListInteractor::class);
    }
}
