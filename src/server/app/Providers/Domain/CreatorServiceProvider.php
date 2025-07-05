<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\Creator\DeleteRequest;
use App\Http\Requests\Web\Creator\EditRequest;
use Creator\Application\Interactors\CreateInteractor;
use Creator\Application\Interactors\DeleteInteractor;
use Creator\Application\Interactors\EditInteractor;
use Creator\Application\Interactors\GetInteractor;
use Creator\Application\Interactors\ListInteractor;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Application\UseCase\Edit\EditInputData;
use Creator\Application\UseCase\Edit\EditUseCaseInterface;
use Creator\Application\UseCase\Get\GetUseCaseInterface;
use Creator\Application\UseCase\List\ListUseCaseInterface;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Infrastructures\CreatorFactory;
use Illuminate\Http\Request;

class CreatorServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CreatorRepositoryInterface::class, FileCreatorRepository::class);
        $this->app->bind(CreatorFactoryInterface::class, CreatorFactory::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(EditUseCaseInterface::class, EditInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });

        $this->app->bind(EditInputData::class, function (): EditInputData {
            $request = $this->app->make(EditRequest::class);

            return $this->getMapper()->map(EditInputData::class, $request->validated());
        });

        $this->app->bind(DeleteInputData::class, function (): DeleteInputData {
            $request = $this->app->make(DeleteRequest::class);

            return $this->getMapper()->map(DeleteInputData::class, $request->validated());
        });
    }
}
