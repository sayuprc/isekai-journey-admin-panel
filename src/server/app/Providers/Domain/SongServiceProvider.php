<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Domain\Services\CreatorUsageCheckerInterface;
use Illuminate\Http\Request;
use Override;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Application\UseCase\Tag\Create\CreateInputData as CreateTagInputData;
use Song\Application\UseCase\Tag\Search\SearchInputData as SearchTagInputData;
use Song\Application\UseCase\Tag\Update\UpdateInputData as UpdateTagInputData;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Infrastructures\CreatorUsageChecker;
use Song\Infrastructures\SongQueryService;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\Tag\SongTagRepository;

class SongServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(SongTagRepositoryInterface::class, SongTagRepository::class);
        $this->app->bind(CreatorUsageCheckerInterface::class, CreatorUsageChecker::class);

        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);

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

        $this->registerSongTag();
    }

    private function registerSongTag(): void
    {
        $this->app->bind(SearchTagInputData::class, function (): SearchTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(SearchTagInputData::class, $request->query());
        });

        $this->app->bind(CreateTagInputData::class, function (): CreateTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateTagInputData::class, $request->all());
        });

        $this->app->bind(UpdateTagInputData::class, function (): UpdateTagInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(
                UpdateTagInputData::class,
                [
                    ...$request->all(),
                    'songTagId' => $request->route('songTagId'),
                ],
            );
        });
    }
}
