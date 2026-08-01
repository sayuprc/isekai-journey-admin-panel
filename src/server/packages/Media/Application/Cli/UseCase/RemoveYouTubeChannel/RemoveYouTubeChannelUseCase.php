<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\RemoveYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Validation\Field;
use Support\Domain\Validation\Fields;

readonly class RemoveYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    public function handle(RemoveYouTubeChannelInputData $inputData): void
    {
        $this->transaction->scope(function () use ($inputData): void {
            $channelIdField = Field::of('channelId', static fn (): YouTubeChannelId => new YouTubeChannelId($inputData->channelId));
            Fields::validate($channelIdField);

            $channelId = $channelIdField->value();

            if (is_null($this->repository->find($channelId))) {
                throw new BusinessRuleViolationException(sprintf('登録されていないチャンネルです "%s"', $channelId->value));
            }

            $this->repository->delete($channelId);
        });
    }
}
