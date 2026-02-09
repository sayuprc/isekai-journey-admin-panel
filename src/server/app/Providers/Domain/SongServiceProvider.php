<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Domain\Services\CreatorUsageCheckerInterface;
use Illuminate\Http\Request;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\Interactors\DeleteInteractor;
use Song\Application\Interactors\GetInteractor;
use Song\Application\Interactors\ListInteractor;
use Song\Application\Interactors\UpdateInteractor;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Application\UseCase\Delete\DeleteUseCaseInterface;
use Song\Application\UseCase\Get\GetUseCaseInterface;
use Song\Application\UseCase\List\ListUseCaseInterface;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Application\UseCase\Update\UpdateUseCaseInterface;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Infrastructures\CreatorUsageChecker;
use Song\Infrastructures\SongFactory;

class SongServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);
        $this->app->bind(SongFactoryInterface::class, SongFactory::class);
        $this->app->bind(CreatorUsageCheckerInterface::class, CreatorUsageChecker::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(UpdateUseCaseInterface::class, UpdateInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });

        $this->app->bind(UpdateInputData::class, function (): UpdateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(
                UpdateInputData::class,
                [
                    'songId' => $request->route('songId'),
                    ...$request->all(),
                ],
            );
        });
    }
}
