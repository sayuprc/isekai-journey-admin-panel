<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\RemoveYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RemoveYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(RemoveYouTubeChannelInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $channelIdResult = YouTubeChannelId::create($inputData->channelId);

            if ($channelIdResult->isErr()) {
                return new Err(new InvalidInputError(['channelId' => [$channelIdResult->unwrapErr()->message]]));
            }

            $channelId = $channelIdResult->unwrap();

            if (is_null($this->repository->find($channelId))) {
                return new Err(new BusinessLogicError(sprintf('登録されていないチャンネルです "%s"', $channelId->value)));
            }

            $this->repository->delete($channelId);

            return new Ok(null);
        });
    }
}
