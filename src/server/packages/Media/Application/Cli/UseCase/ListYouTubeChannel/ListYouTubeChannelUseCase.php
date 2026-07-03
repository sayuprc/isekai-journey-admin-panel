<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ListYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

readonly class ListYouTubeChannelUseCase
{
    public function __construct(private YouTubeChannelRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<ListYouTubeChannelOutputData, UseCaseError>
     */
    public function handle(ListYouTubeChannelInputData $inputData): Result
    {
        return new Ok(new ListYouTubeChannelOutputData($this->repository->findAll()));
    }
}
