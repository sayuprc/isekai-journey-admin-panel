<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Override;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Search\SearchInputData;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Infrastructures\PerformerRepository;

class PerformerServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(PerformerRepositoryInterface::class, PerformerRepository::class);

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
                    'performerId' => $request->route('performerId'),
                ],
            );
        });
    }
}
