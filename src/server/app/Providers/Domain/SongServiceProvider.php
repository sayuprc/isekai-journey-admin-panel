<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\Interactors\ListInteractor;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Application\UseCase\List\ListUseCaseInterface;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Infrastructures\SongFactory;

class SongServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);
        $this->app->bind(SongFactoryInterface::class, SongFactory::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });
    }
}
