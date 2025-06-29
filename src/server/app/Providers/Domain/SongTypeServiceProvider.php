<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use SongType\Application\Interactors\CreateInteractor;
use SongType\Application\Interactors\DeleteInteractor;
use SongType\Application\Interactors\GetInteractor;
use SongType\Application\Interactors\ListInteractor;
use SongType\Application\Interactors\UpdateInteractor;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;
use SongType\Application\UseCase\Delete\DeleteUseCaseInterface;
use SongType\Application\UseCase\Get\GetUseCaseInterface;
use SongType\Application\UseCase\List\ListUseCaseInterface;
use SongType\Application\UseCase\Update\UpdateInputData;
use SongType\Application\UseCase\Update\UpdateUseCaseInterface;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Infrastructures\SongTypeFactory;

class SongTypeServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongTypeFactoryInterface::class, SongTypeFactory::class);

        $this->app->bind(SongTypeRepositoryInterface::class, FileSongTypeRepository::class);

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
                    'songTypeId' => $request->route('songTypeId'),
                    ...$request->all(),
                ]
            );
        });
    }
}
