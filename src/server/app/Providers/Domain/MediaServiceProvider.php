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
            $title = $request->query('title', '');
            $type = $request->query('type');
            $format = $request->query('format');
            $isDisplay = $request->query('is_display');

            return new SearchInputData(
                $request->has('title') && is_string($title) ? $title : Arg::Optional,
                $request->has('type') && is_scalar($type) ? (int)$type : Arg::Optional,
                $request->has('format') && is_scalar($format) ? (int)$format : Arg::Optional,
                $request->has('is_display')
                    ? filter_var($isDisplay, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false
                    : Arg::Optional,
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
