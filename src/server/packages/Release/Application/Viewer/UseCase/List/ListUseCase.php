<?php

declare(strict_types=1);

namespace Release\Application\Viewer\UseCase\List;

use Release\Application\Viewer\Query\ReleaseGroupQueryServiceInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    private const DEFAULT_LIMIT = 50;

    private const MAX_LIMIT = 50;

    public function __construct(private ReleaseGroupQueryServiceInterface $query)
    {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(ListInputData $inputData): Result
    {
        $page = $this->query->list(
            $inputData->cursor,
            min(self::MAX_LIMIT, $inputData->limit ?? self::DEFAULT_LIMIT),
        );

        return new Ok(new ListOutputData($page->releaseGroups, $page->nextCursor));
    }
}
