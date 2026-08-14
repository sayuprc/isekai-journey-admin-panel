<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Application\Cli\UseCase\ImportYouTube;

use Media\Application\Cli\UseCase\ImportYouTube\ChannelImportResult;
use Media\Application\Cli\UseCase\ImportYouTube\ImportYouTubeNotifier;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Color;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\Contracts\NotificationMessage;
use Support\Notification\Contracts\Status;
use Support\Notification\NotificationService;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ImportYouTubeNotifierTest extends TestCase
{
    use EntityFactory;

    private MockInterface&NotificationDriverInterface $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = Mockery::mock(NotificationDriverInterface::class);
    }

    #[Test]
    public function succeededNotifiesWithResultEmbeds(): void
    {
        $expected = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Succeeded,
            content: 'YouTube からの取り込みを実行しました',
            embeds: [
                new NotificationEmbed(
                    title: '成功ch の取り込みが完了',
                    description: '取り込み件数: 2',
                    color: Color::Success,
                    url: 'https://www.youtube.com/channel/UCabcdefghijklmnopqrstuv',
                ),
                new NotificationEmbed(
                    title: '失敗ch に失敗',
                    description: 'チャンネルが見つかりませんでした',
                    color: Color::Error,
                    url: 'https://www.youtube.com/channel/UCabcdefghijklmnopqrstuw',
                ),
                new NotificationEmbed(
                    title: '0件ch の取り込みが完了',
                    description: '取り込み件数: 0',
                    color: Color::Warning,
                    url: 'https://www.youtube.com/channel/UCabcdefghijklmnopqrstux',
                ),
            ],
        );

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(
                static fn (NotificationMessage $message): bool => $message->toJson() === $expected->toJson(),
            ));

        $this->getInstance()->succeeded([
            ChannelImportResult::imported(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstuv', '成功ch'),
                2,
            ),
            ChannelImportResult::channelNotFound(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstuw', '失敗ch'),
            ),
            ChannelImportResult::imported(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstux', '0件ch'),
                0,
            ),
        ]);
    }

    #[Test]
    public function failedNotifiesWithExceptionMessage(): void
    {
        $expected = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: 'YouTube からの取り込みで例外が発生しました',
            embeds: [
                new NotificationEmbed(
                    title: 'boom',
                    color: Color::Error,
                ),
            ],
        );

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(
                static fn (NotificationMessage $message): bool => $message->toJson() === $expected->toJson(),
            ));

        $this->getInstance()->failed(new RuntimeException('boom'));
    }

    private function getInstance(): ImportYouTubeNotifier
    {
        return new ImportYouTubeNotifier(new NotificationService($this->driver));
    }
}
