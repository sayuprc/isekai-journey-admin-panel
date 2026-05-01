<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Domain\Services\CreatorUsageCheckerInterface;
use Illuminate\Http\Request;
use Override;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\Interactors\DeleteInteractor;
use Song\Application\Interactors\GetInteractor;
use Song\Application\Interactors\ListTypeInteractor;
use Song\Application\Interactors\SearchInteractor;
use Song\Application\Interactors\SearchTagInteractor;
use Song\Application\Interactors\Tag\CreateInteractor as CreateTagInteractor;
use Song\Application\Interactors\Tag\DeleteInteractor as DeleteTagInteractor;
use Song\Application\Interactors\Tag\GetInteractor as GetTagInteractor;
use Song\Application\Interactors\Tag\ListTagInteractor;
use Song\Application\Interactors\Tag\UpdateInteractor as UpdateTagInteractor;
use Song\Application\Interactors\UpdateInteractor;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Application\UseCase\Delete\DeleteUseCaseInterface;
use Song\Application\UseCase\Get\GetUseCaseInterface;
use Song\Application\UseCase\ListTag\ListTagUseCaseInterface;
use Song\Application\UseCase\ListType\ListTypeUseCaseInterface;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Application\UseCase\Search\SearchUseCaseInterface;
use Song\Application\UseCase\SearchTag\SearchInputData as SearchTagInputData;
use Song\Application\UseCase\SearchTag\SearchUseCaseInterface as SearchTagUseCaseInterface;
use Song\Application\UseCase\Tag\Create\CreateInputData as CreateSongTagInputData;
use Song\Application\UseCase\Tag\Create\CreateUseCaseInterface as CreateSongTagUseCaseInterface;
use Song\Application\UseCase\Tag\Delete\DeleteUseCaseInterface as DeleteSongTagUseCaseInterface;
use Song\Application\UseCase\Tag\Get\GetUseCaseInterface as GetSongTagUseCaseInterface;
use Song\Application\UseCase\Tag\Update\UpdateInputData as UpdateSongTagInputData;
use Song\Application\UseCase\Tag\Update\UpdateUseCaseInterface as UpdateSongTagUseCaseInterface;
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
use Song\Infrastructures\Tag\SongTagFactory;
use Song\Infrastructures\Tag\SongTagRepository;

class SongServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(SongFactoryInterface::class, SongFactory::class);
        $this->app->bind(SongTagRepositoryInterface::class, SongTagRepository::class);
        $this->app->bind(SongTagFactoryInterface::class, SongTagFactory::class);
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

        $this->registerSongType();

        $this->registerSongTag();
    }

    private function registerSongType(): void
    {
        $this->app->bind(ListTypeUseCaseInterface::class, ListTypeInteractor::class);
    }

    private function registerSongTag(): void
    {
        $this->app->bind(ListTagUseCaseInterface::class, ListTagInteractor::class);

        $this->app->bind(SearchTagUseCaseInterface::class, SearchTagInteractor::class);

        $this->app->bind(SearchTagInputData::class, function (): SearchTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(SearchTagInputData::class, $request->query());
        });

        $this->app->bind(CreateSongTagUseCaseInterface::class, CreateTagInteractor::class);

        $this->app->bind(CreateSongTagInputData::class, function (): CreateSongTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateSongTagInputData::class, $request->all());
        });

        $this->app->bind(GetSongTagUseCaseInterface::class, GetTagInteractor::class);

        $this->app->bind(UpdateSongTagUseCaseInterface::class, UpdateTagInteractor::class);

        $this->app->bind(DeleteSongTagUseCaseInterface::class, DeleteTagInteractor::class);

        $this->app->bind(UpdateSongTagInputData::class, function (): UpdateSongTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(
                UpdateSongTagInputData::class,
                [
                    ...$request->all(),
                    'songTagId' => $request->route('songTagId'),
                ],
            );
        });
    }
}
