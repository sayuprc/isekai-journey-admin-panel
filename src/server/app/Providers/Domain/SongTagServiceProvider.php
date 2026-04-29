<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Override;
use Song\Domain\Models\TagFactoryInterface;
use Song\Domain\Models\TagRepositoryInterface;
use SongTag\Application\Interactors\ListInteractor;
use SongTag\Application\Interactors\SearchInteractor;
use SongTag\Application\UseCase\List\ListUseCaseInterface;
use SongTag\Application\UseCase\Search\SearchInputData;
use SongTag\Application\UseCase\Search\SearchUseCaseInterface;
use SongTag\Infrastructures\TagFactory;
use SongTag\Infrastructures\TagRepository;

class SongTagServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(TagFactoryInterface::class, TagFactory::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(SearchUseCaseInterface::class, SearchInteractor::class);

        $this->app->bind(SearchInputData::class, function (): SearchInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(SearchInputData::class, $request->query());
        });
    }
}
