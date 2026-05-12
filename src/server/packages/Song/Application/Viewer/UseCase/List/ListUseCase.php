<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\List;

use ResultType\Ok;
use ResultType\Result;
use Song\Application\Viewer\Query\SongQueryServiceInterface;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    private const DEFAULT_LIMIT = 50;

    private const MAX_LIMIT = 50;

    public function __construct(private SongQueryServiceInterface $query)
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

        return new Ok(new ListOutputData($page->songs, $page->nextCursor));
    }
}
