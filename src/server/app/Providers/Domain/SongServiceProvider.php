<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\Song\CreateRequest;
use Song\Application\Create\CreateInteractor;
use Song\Application\List\ListInteractor;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Repositories\SongRepositoryInterface;
use Song\Infrastructures\Factories\SongFactory;
use Song\Infrastructures\Repositories\FileSongRepository;
use Song\UseCases\Create\CreateInputData;
use Song\UseCases\Create\CreateUseCaseInterface;
use Song\UseCases\List\ListUseCaseInterface;

class SongServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongFactoryInterface::class, SongFactory::class);
        $this->app->bind(SongRepositoryInterface::class, FileSongRepository::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(CreateRequest::class);
            assert($request instanceof CreateRequest);

            return $this->getMapper()->map(CreateInputData::class, $request->validated());
        });
    }
}
