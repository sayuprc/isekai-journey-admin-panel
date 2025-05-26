<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\Creator\CreateRequest;
use App\Http\Requests\Web\Creator\DeleteRequest;
use App\Http\Requests\Web\Creator\EditRequest;
use Creator\Application\Create\CreateInteractor;
use Creator\Application\Delete\DeleteInteractor;
use Creator\Application\Edit\EditInteractor;
use Creator\Application\Get\GetInteractor;
use Creator\Application\List\ListInteractor;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Infrastructures\Factories\CreatorFactory;
use Creator\Infrastructures\Repositories\FileCreatorRepository;
use Creator\UseCases\Create\CreateInputData;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Creator\UseCases\Delete\DeleteInputData;
use Creator\UseCases\Delete\DeleteUseCaseInterface;
use Creator\UseCases\Edit\EditInputData;
use Creator\UseCases\Edit\EditUseCaseInterface;
use Creator\UseCases\Get\GetUseCaseInterface;
use Creator\UseCases\List\ListUseCaseInterface;

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
            $request = $this->app->make(CreateRequest::class);
            assert($request instanceof CreateRequest);

            return $this->getMapper()->map(CreateInputData::class, $request->validated());
        });

        $this->app->bind(EditInputData::class, function (): EditInputData {
            $request = $this->app->make(EditRequest::class);
            assert($request instanceof EditRequest);

            return $this->getMapper()->map(EditInputData::class, $request->validated());
        });

        $this->app->bind(DeleteInputData::class, function (): DeleteInputData {
            $request = $this->app->make(DeleteRequest::class);
            assert($request instanceof DeleteRequest);

            return $this->getMapper()->map(DeleteInputData::class, $request->validated());
        });
    }
}
