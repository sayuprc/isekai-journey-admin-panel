<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Media\Domain\Models\MediaRepositoryInterface;
use Override;
use Person\Domain\Services\PersonUsageCheckerInterface;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Application\UseCase\Tag\Create\CreateInputData as CreateTagInputData;
use Song\Application\UseCase\Tag\Search\SearchInputData as SearchTagInputData;
use Song\Application\UseCase\Tag\Update\UpdateInputData as UpdateTagInputData;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Domain\Criteria\Sort;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Infrastructures\PersonUsageChecker;
use Song\Infrastructures\SongQueryService;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\Tag\SongTagRepository;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;

class SongServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(SongTagRepositoryInterface::class, SongTagRepository::class);
        $this->app->bind(PersonUsageCheckerInterface::class, PersonUsageChecker::class);
        $this->app->bind(MediaRepositoryInterface::class, \Media\Infrastructures\MediaRepository::class);

        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);

        $this->app->bind(SearchInputData::class, function (): SearchInputData {
            $request = $this->app->make(Request::class);

            return new SearchInputData(
                $request->has('title') ? $request->query('title', '') : Arg::Optional,
                $request->has('type') ? (int)$request->query('type') : Arg::Optional,
                $request->has('is_display')
                    ? filter_var($request->query('is_display'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false
                    : Arg::Optional,
                Sort::from($request->query('sort', Sort::OrderNo->value)),
                Order::from($request->query('order', Order::Asc->value)),
                (int)$request->query('page', 1),
                PerPage::from((int)$request->query('per_page', PerPage::Fifty->value)),
            );
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
