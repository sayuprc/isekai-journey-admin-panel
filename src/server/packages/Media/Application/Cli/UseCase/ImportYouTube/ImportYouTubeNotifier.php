<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Color;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\Status;
use Support\Notification\NotificationService;
use Throwable;

readonly class ImportYouTubeNotifier
{
    public function __construct(private NotificationService $notifier)
    {
    }

    /**
     * @param list<ChannelImportResult> $results
     */
    public function succeeded(array $results): void
    {
        $this->notifier->notice(
            Action::MediaYoutubeImport,
            Status::Succeeded,
            content: 'YouTube からの取り込みを実行しました',
            embeds: array_map(
                fn (ChannelImportResult $result): NotificationEmbed => $this->buildEmbed($result),
                $results,
            ),
        );
    }

    public function failed(Throwable $e): void
    {
        $this->notifier->notice(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: 'YouTube からの取り込みで例外が発生しました',
            embeds: [
                new NotificationEmbed(
                    title: $e->getMessage(),
                    color: Color::Error,
                ),
            ],
        );
    }

    private function buildEmbed(ChannelImportResult $result): NotificationEmbed
    {
        if ($result->channelFound) {
            return new NotificationEmbed(
                title: sprintf('%s の取り込みが完了', $result->channel->name->value),
                description: sprintf('取り込み件数: %d', $result->importedCount),
                color: $result->importedCount === 0 ? Color::Warning : Color::Success,
                url: $this->buildChannelUrl($result->channel->channelId->value),
            );
        }

        return new NotificationEmbed(
            title: sprintf('%s に失敗', $result->channel->name->value),
            description: 'チャンネルが見つかりませんでした',
            color: Color::Error,
            url: $this->buildChannelUrl($result->channel->channelId->value),
        );
    }

    private function buildChannelUrl(string $channelId): string
    {
        return sprintf('https://www.youtube.com/channel/%s', $channelId);
    }
}
