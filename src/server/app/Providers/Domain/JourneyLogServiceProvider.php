<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use JourneyLog\Application\Interactors\CreateInteractor;
use JourneyLog\Application\Interactors\DeleteInteractor;
use JourneyLog\Application\Interactors\EditInteractor;
use JourneyLog\Application\Interactors\GetInteractor;
use JourneyLog\Application\Interactors\ListInteractor;
use JourneyLog\Application\UseCase\Create\CreateInputData;
use JourneyLog\Application\UseCase\Create\CreateUseCaseInterface;
use JourneyLog\Application\UseCase\Delete\DeleteInputData;
use JourneyLog\Application\UseCase\Delete\DeleteUseCaseInterface;
use JourneyLog\Application\UseCase\Edit\EditInputData;
use JourneyLog\Application\UseCase\Edit\EditUseCaseInterface;
use JourneyLog\Application\UseCase\Get\GetUseCaseInterface;
use JourneyLog\Application\UseCase\List\ListUseCaseInterface;
use JourneyLog\DebugInfrastructures\FileJourneyLogRepository;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\JourneyLogFactory;

class JourneyLogServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyLogRepositoryInterface::class, FileJourneyLogRepository::class);
        $this->app->bind(JourneyLogFactoryInterface::class, JourneyLogFactory::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(EditUseCaseInterface::class, EditInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });

        $this->app->bind(EditInputData::class, function (): EditInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(EditInputData::class, $request->all());
        });

        $this->app->bind(DeleteInputData::class, function (): DeleteInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(DeleteInputData::class, $request->all());
        });
    }
}
