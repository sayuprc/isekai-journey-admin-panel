<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use SongType\Application\Create\CreateInteractor;
use SongType\Application\Delete\DeleteInteractor;
use SongType\Application\Edit\EditInteractor;
use SongType\Application\Get\GetInteractor;
use SongType\Application\List\ListInteractor;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Infrastructures\Factories\SongTypeFactory;
use SongType\Infrastructures\Repositories\FileSongTypeRepository;
use SongType\UseCases\Create\CreateRequest;
use SongType\UseCases\Create\CreateUseCaseInterface;
use SongType\UseCases\Delete\DeleteRequest;
use SongType\UseCases\Delete\DeleteUseCaseInterface;
use SongType\UseCases\Edit\EditRequest;
use SongType\UseCases\Edit\EditUseCaseInterface;
use SongType\UseCases\Get\GetUseCaseInterface;
use SongType\UseCases\List\ListUseCaseInterface;

class SongTypeServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SongTypeFactoryInterface::class, SongTypeFactory::class);

        $this->app->bind(SongTypeRepositoryInterface::class, FileSongTypeRepository::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(EditUseCaseInterface::class, EditInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);

        $this->app->bind(CreateRequest::class, function (): CreateRequest {
            $request = $this->app->make(\App\Http\Requests\Web\SongType\CreateRequest::class);
            assert($request instanceof \App\Http\Requests\Web\SongType\CreateRequest);

            return $this->getMapper()->map(CreateRequest::class, $request->validated());
        });

        $this->app->bind(EditRequest::class, function (): EditRequest {
            $request = $this->app->make(\App\Http\Requests\Web\SongType\EditRequest::class);
            assert($request instanceof \App\Http\Requests\Web\SongType\EditRequest);

            return $this->getMapper()->map(EditRequest::class, $request->validated());
        });

        $this->app->bind(DeleteRequest::class, function (): DeleteRequest {
            $request = $this->app->make(\App\Http\Requests\Web\SongType\DeleteRequest::class);
            assert($request instanceof \App\Http\Requests\Web\SongType\DeleteRequest);

            return $this->getMapper()->map(DeleteRequest::class, $request->validated());
        });
    }
}
