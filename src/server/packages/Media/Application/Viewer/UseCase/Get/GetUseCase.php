<?php

declare(strict_types=1);

namespace Media\Application\Viewer\UseCase\Get;

use Media\Application\Viewer\Query\MediaQueryServiceInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(private MediaQueryServiceInterface $query)
    {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        $media = $this->query->get($inputData->mediaId);

        if (is_null($media)) {
            return new Err(new NotFoundError('メディア', $inputData->mediaId));
        }

        return new Ok(new GetOutputData($media));
    }
}
