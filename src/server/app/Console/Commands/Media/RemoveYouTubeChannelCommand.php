<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use App\Console\Commands\Concerns\ResolvesUseCaseErrorMessage;
use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\RemoveYouTubeChannel\RemoveYouTubeChannelInputData;
use Media\Application\Cli\UseCase\RemoveYouTubeChannel\RemoveYouTubeChannelUseCase;
use Override;

class RemoveYouTubeChannelCommand extends Command
{
    use ResolvesUseCaseErrorMessage;

    #[Override]
    protected $signature = 'media:youtube-channel:remove {channelId}';

    #[Override]
    protected $description = 'インポート対象の YouTube チャンネルを削除する';

    public function handle(RemoveYouTubeChannelUseCase $useCase): int
    {
        $channelId = $this->argument('channelId');

        $result = $useCase->handle(new RemoveYouTubeChannelInputData($channelId));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $this->info(sprintf('チャンネルを削除しました: %s', $channelId));

        return Command::SUCCESS;
    }
}
