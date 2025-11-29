<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Performer\Application\Interactors\CreateInteractor;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
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

        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });
    }
}
