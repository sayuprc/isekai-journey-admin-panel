<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\JourneyLogLinkType\CreateRequest;
use App\Http\Requests\Web\JourneyLogLinkType\DeleteRequest;
use App\Http\Requests\Web\JourneyLogLinkType\EditRequest;
use JourneyLogLinkType\Application\Create\CreateInteractor;
use JourneyLogLinkType\Application\Delete\DeleteInteractor;
use JourneyLogLinkType\Application\Edit\EditInteractor;
use JourneyLogLinkType\Application\Get\GetInteractor;
use JourneyLogLinkType\Application\List\ListInteractor;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\Factories\JourneyLogLinkTypeFactory;
use JourneyLogLinkType\Infrastructures\Repositories\FileJourneyLogLinkTypeRepository;
use JourneyLogLinkType\UseCases\Create\CreateInputData;
use JourneyLogLinkType\UseCases\Create\CreateUseCaseInterface;
use JourneyLogLinkType\UseCases\Delete\DeleteInputData;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;
use JourneyLogLinkType\UseCases\Edit\EditInputData;
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
