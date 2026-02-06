<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Song\Application\Interactors\CreateInteractor;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Infrastructures\SongFactory;

class SongServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);
        $this->app->bind(SongFactoryInterface::class, SongFactory::class);

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
    }
}
