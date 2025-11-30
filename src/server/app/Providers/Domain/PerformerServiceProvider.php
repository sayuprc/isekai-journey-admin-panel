<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\Interactors\DeleteInteractor;
use Performer\Application\Interactors\GetInteractor;
use Performer\Application\Interactors\ListInteractor;
use Performer\Application\Interactors\UpdateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
use Performer\Application\UseCase\Delete\DeleteUseCaseInterface;
use Performer\Application\UseCase\Get\GetUseCaseInterface;
use Performer\Application\UseCase\List\ListUseCaseInterface;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateUseCaseInterface;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Infrastructures\PerformerFactory;

class PerformerServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PerformerRepositoryInterface::class, FilePerformerRepository::class);
        $this->app->bind(PerformerFactoryInterface::class, PerformerFactory::class);

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
                    'performerId' => $request->route('performerId'),
                    ...$request->all(),
                ]
            );
        });
    }
}
