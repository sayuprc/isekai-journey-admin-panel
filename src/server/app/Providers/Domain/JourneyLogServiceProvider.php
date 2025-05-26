<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\JourneyLog\CreateRequest;
use App\Http\Requests\Web\JourneyLog\DeleteRequest;
use App\Http\Requests\Web\JourneyLog\EditRequest;
use JourneyLog\Application\Create\CreateInteractor;
use JourneyLog\Application\Delete\DeleteInteractor;
use JourneyLog\Application\Edit\EditInteractor;
use JourneyLog\Application\Get\GetInteractor;
use JourneyLog\Application\List\ListInteractor;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Infrastructures\Factories\JourneyLogFactory;
use JourneyLog\Infrastructures\Repositories\FileJourneyLogRepository;
use JourneyLog\UseCases\Create\CreateInputData;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;
use JourneyLog\UseCases\Delete\DeleteInputData;
use JourneyLog\UseCases\Delete\DeleteUseCaseInterface;
use JourneyLog\UseCases\Edit\EditInputData;
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
