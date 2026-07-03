<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\AddYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelName;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class AddYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<AddYouTubeChannelOutputData, UseCaseError>
     */
    public function handle(AddYouTubeChannelInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $channelIdResult = YouTubeChannelId::create($inputData->channelId);

            if ($channelIdResult->isErr()) {
                return new Err(new InvalidInputError(['channelId' => [$channelIdResult->unwrapErr()->message]]));
            }

            $nameResult = YouTubeChannelName::create($inputData->name);

            if ($nameResult->isErr()) {
                return new Err(new InvalidInputError(['name' => [$nameResult->unwrapErr()->message]]));
            }

            $channel = new YouTubeChannel($channelIdResult->unwrap(), $nameResult->unwrap());

            if (! is_null($this->repository->find($channel->channelId))) {
                return new Err(new BusinessLogicError(sprintf('すでに登録されているチャンネルです "%s"', $channel->channelId->value)));
            }

            return new Ok(new AddYouTubeChannelOutputData($this->repository->save($channel)));
        });
    }
}
