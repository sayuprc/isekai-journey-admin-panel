<?php

declare(strict_types=1);

namespace App\Providers\EnvProviders;

use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Infrastructures\Repositories\FileCreatorRepository;
use Illuminate\Support\ServiceProvider;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\Repositories\FileJourneyLogRepository;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\Repositories\FileJourneyLogLinkTypeRepository;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\Infrastructures\Repositories\FileSongRepository;
use Support\Application\Uuid\DummyUuidGenerator;
use Support\Uuid\UuidGeneratorInterface;

class ProdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UuidGeneratorInterface::class, DummyUuidGenerator::class);

        $this->app->bind(JourneyLogRepositoryInterface::class, FileJourneyLogRepository::class);

        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, FileJourneyLogLinkTypeRepository::class);

        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);

        // TODO 正式なリポジトリを作成したら差し替える
        $this->app->bind(CreatorRepositoryInterface::class, FileCreatorRepository::class);

        $this->auth();

        $this->journeyLog();

        $this->journeyLogLinkType();

        $this->song();

        $this->creator();
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
        $this->app->bind(\JourneyLog\Domain\Models\JourneyLogFactoryInterface::class, \JourneyLog\Infrastructures\Factories\JourneyLogFactory::class);

        $this->app->bind(\JourneyLog\UseCases\List\ListUseCaseInterface::class, \JourneyLog\Application\List\ListInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Get\GetUseCaseInterface::class, \JourneyLog\Application\Get\GetInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Create\CreateUseCaseInterface::class, \JourneyLog\Application\Create\CreateInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Edit\EditUseCaseInterface::class, \JourneyLog\Application\Edit\EditInteractor::class);
        $this->app->bind(\JourneyLog\UseCases\Delete\DeleteUseCaseInterface::class, \JourneyLog\Application\Delete\DeleteInteractor::class);
    }

    private function journeyLogLinkType(): void
    {
        $this->app->bind(\JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface::class, \JourneyLogLinkType\Infrastructures\Factories\JourneyLogLinkTypeFactory::class);

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

    private function creator(): void
    {
        $this->app->bind(\Creator\Domain\Models\CreatorFactoryInterface::class, \Creator\Infrastructures\Factories\CreatorFactory::class);

        $this->app->bind(\Creator\UseCases\List\ListUseCaseInterface::class, \Creator\Application\List\ListInteractor::class);
        $this->app->bind(\Creator\UseCases\Create\CreateUseCaseInterface::class, \Creator\Application\Create\CreateInteractor::class);
        $this->app->bind(\Creator\UseCases\Get\GetUseCaseInterface::class, \Creator\Application\Get\GetInteractor::class);
        $this->app->bind(\Creator\UseCases\Edit\EditUseCaseInterface::class, \Creator\Application\Edit\EditInteractor::class);
        $this->app->bind(\Creator\UseCases\Delete\DeleteUseCaseInterface::class, \Creator\Application\Delete\DeleteInteractor::class);
    }
}
