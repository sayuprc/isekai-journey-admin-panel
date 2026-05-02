<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Application\Interactors\CreateInteractor;
use Creator\Application\Interactors\DeleteInteractor;
use Creator\Application\Interactors\GetInteractor;
use Creator\Application\Interactors\ListInteractor;
use Creator\Application\Interactors\SearchInteractor;
use Creator\Application\Interactors\UpdateInteractor;
use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Application\UseCase\Get\GetUseCaseInterface;
use Creator\Application\UseCase\List\ListUseCaseInterface;
use Creator\Application\UseCase\Search\SearchInputData;
use Creator\Application\UseCase\Search\SearchUseCaseInterface;
use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Infrastructures\CreatorRepository;
use Illuminate\Http\Request;
use Override;

class CreatorServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(CreatorRepositoryInterface::class, CreatorRepository::class);

        $this->app->bind(ListUseCaseInterface::class, ListInteractor::class);
        $this->app->bind(SearchUseCaseInterface::class, SearchInteractor::class);
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
                    'creatorId' => $request->route('creatorId'),
                ],
            );
        });
    }
}
