<?php

declare(strict_types=1);

namespace SiteStats\Application\Viewer\UseCase\Get;

use ResultType\Ok;
use ResultType\Result;
use SiteStats\Application\Viewer\Query\SiteStatsQueryServiceInterface;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(private SiteStatsQueryServiceInterface $query)
    {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        $siteStats = $this->query->get();

        return new Ok(new GetOutputData($siteStats->songCount));
    }
}
