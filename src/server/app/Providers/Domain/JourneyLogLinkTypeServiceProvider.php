<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\JourneyLogLinkType\CreateRequest;
use App\Http\Requests\Web\JourneyLogLinkType\DeleteRequest;
use App\Http\Requests\Web\JourneyLogLinkType\EditRequest;
use JourneyLogLinkType\Application\Interactors\CreateInteractor;
use JourneyLogLinkType\Application\Interactors\DeleteInteractor;
use JourneyLogLinkType\Application\Interactors\EditInteractor;
use JourneyLogLinkType\Application\Interactors\GetInteractor;
use JourneyLogLinkType\Application\Interactors\ListInteractor;
use JourneyLogLinkType\Application\UseCase\Create\CreateInputData;
use JourneyLogLinkType\Application\UseCase\Create\CreateUseCaseInterface;
use JourneyLogLinkType\Application\UseCase\Delete\DeleteInputData;
use JourneyLogLinkType\Application\UseCase\Delete\DeleteUseCaseInterface;
use JourneyLogLinkType\Application\UseCase\Edit\EditInputData;
use JourneyLogLinkType\Application\UseCase\Edit\EditUseCaseInterface;
use JourneyLogLinkType\Application\UseCase\Get\GetUseCaseInterface;
use JourneyLogLinkType\Application\UseCase\List\ListUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeFactoryInterface;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\Infrastructures\JourneyLogLinkTypeFactory;
use JourneyLogLinkType\Infrastructures\Repositories\FileJourneyLogLinkTypeRepository;

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

            return $this->getMapper()->map(CreateInputData::class, $request->validated());
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
