<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use JourneyLog\Application\Create\CreateInteractor;
use JourneyLog\Application\Delete\DeleteInteractor;
use JourneyLog\Application\Edit\EditInteractor;
use JourneyLog\Application\Get\GetInteractor;
use JourneyLog\Application\List\ListInteractor;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\Factories\JourneyLogFactory;
use JourneyLog\Infrastructures\Repositories\FileJourneyLogRepository;
use JourneyLog\UseCases\Create\CreateRequest;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;
use JourneyLog\UseCases\Delete\DeleteRequest;
use JourneyLog\UseCases\Delete\DeleteUseCaseInterface;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
use JourneyLog\UseCases\List\ListUseCaseInterface;

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

        $this->app->bind(CreateRequest::class, function (): CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLog\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLog\CreateRequest);

            return $this->getMapper()->map(CreateRequest::class, $request->validated());
        });

        $this->app->bind(EditRequest::class, function (): EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLog\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLog\EditRequest);

            return $this->getMapper()->map(EditRequest::class, $request->validated());
        });

        $this->app->bind(DeleteRequest::class, function (): DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLog\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLog\DeleteRequest);

            return $this->getMapper()->map(DeleteRequest::class, $request->validated());
        });
    }
}
