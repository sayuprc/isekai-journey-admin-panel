<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\SongType\CreateRequest;
use App\Http\Requests\Web\SongType\DeleteRequest;
use App\Http\Requests\Web\SongType\EditRequest;
use SongType\Application\Create\CreateInteractor;
use SongType\Application\Delete\DeleteInteractor;
use SongType\Application\Edit\EditInteractor;
use SongType\Application\Get\GetInteractor;
use SongType\Application\List\ListInteractor;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\Infrastructures\Factories\SongTypeFactory;
use SongType\Infrastructures\Repositories\FileSongTypeRepository;
use SongType\UseCases\Create\CreateInputData;
use SongType\UseCases\Create\CreateUseCaseInterface;
use SongType\UseCases\Delete\DeleteInputData;
use SongType\UseCases\Delete\DeleteUseCaseInterface;
use SongType\UseCases\Edit\EditInputData;
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
