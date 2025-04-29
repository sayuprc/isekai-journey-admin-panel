<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Application\Create\CreateInteractor;
use Creator\Application\Delete\DeleteInteractor;
use Creator\Application\Edit\EditInteractor;
use Creator\Application\Get\GetInteractor;
use Creator\Application\List\ListInteractor;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Infrastructures\Factories\CreatorFactory;
use Creator\Infrastructures\Repositories\FileCreatorRepository;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Creator\UseCases\Delete\DeleteRequest;
use Creator\UseCases\Delete\DeleteUseCaseInterface;
use Creator\UseCases\Edit\EditRequest;
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

        $this->app->bind(CreateRequest::class, function (): CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Creator\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Creator\CreateRequest);

            return $this->getMapper()->map(CreateRequest::class, $request->validated());
        });

        $this->app->bind(EditRequest::class, function (): EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Creator\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Creator\EditRequest);

            return $this->getMapper()->map(EditRequest::class, $request->validated());
        });

        $this->app->bind(DeleteRequest::class, function (): DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Creator\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Creator\DeleteRequest);

            return $this->getMapper()->map(DeleteRequest::class, $request->validated());
        });
    }
}
