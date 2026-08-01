<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\UpdateYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelName;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Validation\Field;
use Support\Domain\Validation\Fields;

readonly class UpdateYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateYouTubeChannelInputData $inputData): UpdateYouTubeChannelOutputData
    {
        return $this->transaction->scope(function () use ($inputData): UpdateYouTubeChannelOutputData {
            $channelIdField = Field::of('channelId', static fn (): YouTubeChannelId => new YouTubeChannelId($inputData->channelId));
            $nameField = Field::of('name', static fn (): YouTubeChannelName => new YouTubeChannelName($inputData->name));
            Fields::validate($channelIdField, $nameField);

            $channelId = $channelIdField->value();

            if (is_null($this->repository->find($channelId))) {
                throw new BusinessRuleViolationException(sprintf('登録されていないチャンネルです "%s"', $channelId->value));
            }

            $channel = new YouTubeChannel($channelId, $nameField->value());

            return new UpdateYouTubeChannelOutputData($this->repository->save($channel));
        });
    }
}
