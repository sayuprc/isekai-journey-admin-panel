<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

use DateType\ImmutableDate;
use Media\Application\Cli\Query\YouTubeVideoQueryServiceInterface;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\MediaUrl;
use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\UseCase\Error\UseCaseError;

readonly class ImportYouTubeUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private UuidGeneratorInterface $generator,
        private YouTubeChannelRepositoryInterface $channelRepository,
        private YouTubeVideoQueryServiceInterface $videoQueryService,
        private MediaRepositoryInterface $mediaRepository,
    ) {
    }

    /**
     * @return Result<ImportYouTubeOutputData, UseCaseError>
     */
    public function handle(ImportYouTubeInputData $inputData): Result
    {
        $results = [];

        foreach ($this->channelRepository->findAll() as $channel) {
            $results[] = $this->importChannel($channel);
        }

        return new Ok(new ImportYouTubeOutputData($results));
    }

    private function importChannel(YouTubeChannel $channel): ChannelImportResult
    {
        $videos = $this->videoQueryService->fetchUploadedVideos($channel->channelId);

        if (is_null($videos)) {
            return ChannelImportResult::channelNotFound($channel);
        }

        $mediaList = [];

        foreach ($videos as $video) {
            $url = MediaUrl::reconstruct('https://www.youtube.com/watch?v=' . $video->videoId);

            // 動画は新しい順に取得されるため、保存済みの動画に到達した時点で以降は取り込み済みとみなす
            if (! is_null($this->mediaRepository->findByUrl($url))) {
                break;
            }

            $mediaList[] = Media::reconstruct(
                $this->generator->generate(),
                $video->title,
                $url->value,
                new ImmutableDate($video->publishedAt),
                $this->resolveType($video->title)->value,
                true,
            );
        }

        $this->transaction->scope(function () use ($mediaList): void {
            foreach ($mediaList as $media) {
                $this->mediaRepository->save($media);
            }
        });

        return ChannelImportResult::imported($channel, count($mediaList));
    }

    private function resolveType(string $title): MediaType
    {
        return match (true) {
            str_contains($title, '#shorts') => MediaType::Short,
            str_contains($title, '【Official Music Video】') => MediaType::Mv,
            str_contains($title, '【歌ってみた】') => MediaType::AudioVideo,
            default => MediaType::Other,
        };
    }
}
