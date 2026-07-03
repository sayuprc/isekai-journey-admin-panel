<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use App\Console\Commands\Concerns\ResolvesUseCaseErrorMessage;
use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\AddYouTubeChannel\AddYouTubeChannelInputData;
use Media\Application\Cli\UseCase\AddYouTubeChannel\AddYouTubeChannelUseCase;
use Override;

class AddYouTubeChannelCommand extends Command
{
    use ResolvesUseCaseErrorMessage;

    #[Override]
    protected $signature = 'media:youtube-channel:add {channelId} {name}';

    #[Override]
    protected $description = 'インポート対象の YouTube チャンネルを追加する';

    public function handle(AddYouTubeChannelUseCase $useCase): int
    {
        $name = $this->argument('name');

        if (mb_trim($name) === '') {
            $this->error('チャンネル名を入力してください');

            return Command::FAILURE;
        }

        $result = $useCase->handle(new AddYouTubeChannelInputData($this->argument('channelId'), $name));

        if ($result->isErr()) {
            $this->error($this->resolveErrorMessage($result->unwrapErr()));

            return Command::FAILURE;
        }

        $channel = $result->unwrap()->channel;

        $this->info(sprintf('チャンネルを追加しました: %s (%s)', $channel->name->value, $channel->channelId->value));

        return Command::SUCCESS;
    }
}
