<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

use DateTimeImmutable;
use DateTimeZone;
use Media\Application\Cli\Query\YouTubeShortVideoQueryServiceInterface;
use Media\Application\Cli\Query\YouTubeUploadedVideo;
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
        private YouTubeShortVideoQueryServiceInterface $shortVideoQueryService,
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

        $videosToImport = [];

        foreach ($videos as $video) {
            $url = MediaUrl::reconstruct($video->url);

            // 動画は新しい順に取得されるため、保存済みの動画に到達した時点で以降は取り込み済みとみなす
            if (! is_null($this->mediaRepository->findByUrl($url))) {
                break;
            }

            $videosToImport[] = $video;
        }

        $shortsByVideoId = $this->shortVideoQueryService->detectShorts(
            array_values(array_filter($videosToImport, fn (YouTubeUploadedVideo $video): bool => ! $this->isKnownShort($video))),
        );

        $mediaList = [];

        foreach ($videosToImport as $video) {
            $url = MediaUrl::reconstruct($video->url);
            $isShort = $this->isKnownShort($video) || ($shortsByVideoId[$video->videoId] ?? false);

            $mediaList[] = Media::reconstruct(
                $this->generator->generate(),
                $video->title,
                $url->value,
                new DateTimeImmutable($video->publishedAt)->setTimezone(new DateTimeZone(date_default_timezone_get())),
                $this->resolveType($video->title, $isShort)->value,
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

    private function resolveType(string $title, bool $isShort): MediaType
    {
        return match (true) {
            $isShort => MediaType::Short,
            str_contains($title, '【Official Music Video】') => MediaType::Mv,
            str_contains($title, '【歌ってみた】') => MediaType::AudioVideo,
            default => MediaType::Other,
        };
    }

    private function isKnownShort(YouTubeUploadedVideo $video): bool
    {
        return str_contains(mb_strtolower($video->title), '#short');
    }
}
