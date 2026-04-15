<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Domain\Services\CreatorUsageCheckerInterface;
use Illuminate\Http\Request;
use Override;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\Interactors\DeleteInteractor;
use Song\Application\Interactors\GetInteractor;
use Song\Application\Interactors\ListAttributeInteractor;
use Song\Application\Interactors\ListTypeInteractor;
use Song\Application\Interactors\SearchInteractor;
use Song\Application\Interactors\UpdateInteractor;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Application\UseCase\Delete\DeleteUseCaseInterface;
use Song\Application\UseCase\Get\GetUseCaseInterface;
use Song\Application\UseCase\ListAttribute\ListAttributeUseCaseInterface;
use Song\Application\UseCase\ListType\ListTypeUseCaseInterface;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Application\UseCase\Search\SearchUseCaseInterface;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Application\UseCase\Update\UpdateUseCaseInterface;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Infrastructures\CreatorUsageChecker;
use Song\Infrastructures\SongFactory;
use Song\Infrastructures\SongQueryService;
use Song\Infrastructures\SongRepository;

class SongServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(SongFactoryInterface::class, SongFactory::class);
        $this->app->bind(CreatorUsageCheckerInterface::class, CreatorUsageChecker::class);

        $this->app->bind(SearchUseCaseInterface::class, SearchInteractor::class);
        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);
        $this->app->bind(CreateUseCaseInterface::class, CreateInteractor::class);
        $this->app->bind(GetUseCaseInterface::class, GetInteractor::class);
        $this->app->bind(UpdateUseCaseInterface::class, UpdateInteractor::class);
        $this->app->bind(DeleteUseCaseInterface::class, DeleteInteractor::class);

        $this->app->bind(SearchInputData::class, function (): SearchInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(SearchInputData::class, $request->query());
        });

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });

        $this->app->bind(UpdateInputData::class, function (): UpdateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(
                UpdateInputData::class,
                [
                    ...$request->all(),
                    'songId' => $request->route('songId'),
                ],
            );
        });

        $this->registerSongAttribute();

        $this->registerSongType();
    }

    private function registerSongAttribute(): void
    {
        $this->app->bind(ListAttributeUseCaseInterface::class, ListAttributeInteractor::class);
    }

    private function registerSongType(): void
    {
        $this->app->bind(ListTypeUseCaseInterface::class, ListTypeInteractor::class);
    }
}
