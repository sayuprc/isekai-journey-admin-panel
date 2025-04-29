<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use JourneyLogLinkType\Application\Create\CreateInteractor;
use JourneyLogLinkType\Application\Delete\DeleteInteractor;
use JourneyLogLinkType\Application\Edit\EditInteractor;
use JourneyLogLinkType\Application\Get\GetInteractor;
use JourneyLogLinkType\Application\List\ListInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\Factories\JourneyLogLinkTypeFactory;
use JourneyLogLinkType\Infrastructures\Repositories\FileJourneyLogLinkTypeRepository;
use JourneyLogLinkType\UseCases\Create\CreateRequest;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;
use JourneyLogLinkType\UseCases\Delete\DeleteRequest;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;

class JourneyLogLinkTypeServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JourneyLogLinkTypeRepositoryInterface::class, FileJourneyLogLinkTypeRepository::class);
        $this->app->bind(JourneyLogLinkTypeFactoryInterface::class, JourneyLogLinkTypeFactory::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(EditUseCaseInterface::class, EditInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);

        $this->app->bind(CreateRequest::class, function (): CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLogLinkType\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLogLinkType\CreateRequest);

            return $this->getMapper()->map(CreateRequest::class, $request->validated());
        });

        $this->app->bind(EditRequest::class, function (): EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLogLinkType\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLogLinkType\EditRequest);

            return $this->getMapper()->map(EditRequest::class, $request->validated());
        });

        $this->app->bind(DeleteRequest::class, function (): DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\JourneyLogLinkType\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\JourneyLogLinkType\DeleteRequest);

            return $this->getMapper()->map(DeleteRequest::class, $request->validated());
        });
    }
}
