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
use Song\Application\Interactors\Tag\CreateInteractor as CreateTagInteractor;
use Song\Application\Interactors\Tag\ListInteractor as ListTagInteractor;
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
use Song\Application\UseCase\Tag\Create\CreateInputData as CreateTagInputData;
use Song\Application\UseCase\Tag\Create\CreateUseCaseInterface as CreateTagUseCaseInterface;
use Song\Application\UseCase\Tag\List\ListUseCaseInterface as ListTagUseCaseInterface;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Application\UseCase\Update\UpdateUseCaseInterface;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTagFactoryInterface;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Infrastructures\CreatorUsageChecker;
use Song\Infrastructures\SongFactory;
use Song\Infrastructures\SongQueryService;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\SongTagFactory;
use Song\Infrastructures\SongTagRepository;

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
                    'songId' => $request->route('songId'),
                    ...$request->all(),
                ],
            );
        });

        $this->registerSongAttribute();

        $this->registerSongTag();

        $this->registerSongType();
    }

    private function registerSongAttribute(): void
    {
        $this->app->bind(ListAttributeUseCaseInterface::class, ListAttributeInteractor::class);
    }

    private function registerSongTag(): void
    {
        $this->app->bind(SongTagRepositoryInterface::class, SongTagRepository::class);
        $this->app->bind(SongTagFactoryInterface::class, SongTagFactory::class);

        $this->app->bind(ListTagUseCaseInterface::class, ListTagInteractor::class);
        $this->app->bind(CreateTagUseCaseInterface::class, CreateTagInteractor::class);

        $this->app->bind(CreateTagInputData::class, function (): CreateTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateTagInputData::class, $request->all());
        });
    }

    private function registerSongType(): void
    {
        $this->app->bind(ListTypeUseCaseInterface::class, ListTypeInteractor::class);
    }
}
