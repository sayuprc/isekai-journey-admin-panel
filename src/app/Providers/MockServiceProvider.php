<?php

declare(strict_types=1);

namespace App\Providers;

use Auth\Application\Login\LoginInteractor;
use Auth\UseCases\Login\LoginUseCaseInterface;
use Illuminate\Support\ServiceProvider;
use JourneyLog\Application\Create\CreateInteractor;
use JourneyLog\Application\Delete\DeleteInteractor;
use JourneyLog\Application\Edit\EditInteractor;
use JourneyLog\Application\Get\GetInteractor;
use JourneyLog\Application\List\ListInteractor;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\Repositories\FileJourneyLogRepository;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;
use JourneyLog\UseCases\Delete\DeleteUseCaseInterface;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
use JourneyLog\UseCases\List\ListUseCaseInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\Repositories\FileJourneyLogLinkTypeRepository;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\Infrastructures\Repositories\FileSongRepository;

class MockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, FileJourneyLogRepository::class);

        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, FileJourneyLogLinkTypeRepository::class);

        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);

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
        $this->app->bind(LoginUseCaseInterface::class, LoginInteractor::class);
    }

    private function journeyLog(): void
    {
        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(EditUseCaseInterface::class, EditInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);
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
