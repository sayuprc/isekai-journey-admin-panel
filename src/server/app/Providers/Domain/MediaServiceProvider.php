<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Http\Request;
use Media\Application\UseCase\Create\CreateInputData;
use Media\Application\UseCase\Search\SearchInputData;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Infrastructures\MediaRepository;
use Override;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;

class MediaServiceProvider extends EnvServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);

        $this->app->bind(SearchInputData::class, function (): SearchInputData {
            $request = $this->app->make(Request::class);

            return new SearchInputData(
                $request->has('title') ? $request->query('title', '') : Arg::Optional,
                (int)$request->query('page', 1),
                PerPage::from((int)$request->query('per_page', PerPage::TwentyFive->value)),
            );
        });

        $this->app->bind(CreateInputData::class, function (): CreateInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(CreateInputData::class, $request->all());
        });
    }
}
