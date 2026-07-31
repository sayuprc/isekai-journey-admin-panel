<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\AddYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelName;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Validation\FieldErrors;

readonly class AddYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    public function handle(AddYouTubeChannelInputData $inputData): AddYouTubeChannelOutputData
    {
        return $this->transaction->scope(function () use ($inputData): AddYouTubeChannelOutputData {
            $errors = new FieldErrors();
            $channelId = $errors->collect('channelId', static fn (): YouTubeChannelId => new YouTubeChannelId($inputData->channelId));
            $name = $errors->collect('name', static fn (): YouTubeChannelName => new YouTubeChannelName($inputData->name));
            $errors->throwIfFailed();

            assert(! is_null($channelId) && ! is_null($name));

            $channel = new YouTubeChannel($channelId, $name);

            if (! is_null($this->repository->find($channel->channelId))) {
                throw new BusinessRuleViolationException(sprintf('すでに登録されているチャンネルです "%s"', $channel->channelId->value));
            }

            return new AddYouTubeChannelOutputData($this->repository->save($channel));
        });
    }
}
