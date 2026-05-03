<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Search\SearchInputData;
use Creator\Application\UseCase\Update\UpdateInputData;
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
