<?php

declare(strict_types=1);

namespace Song\Infrastructures\Factories;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Dtos\CreateNonLinkArchiveData;
use Song\Domain\Dtos\CreateTwitterArchiveData;
use Song\Domain\Dtos\CreateYouTubeArchiveData;
use Song\Domain\Models\Archives\ArchivedOn;
use Song\Domain\Models\Archives\ArchiveId;
use Song\Domain\Models\Archives\ArchiveName;
use Song\Domain\Models\Archives\NonLink\NonLinkArchive;
use Song\Domain\Models\Archives\Twitter\PostUrl;
use Song\Domain\Models\Archives\Twitter\TwitterArchive;
use Song\Domain\Models\Archives\YouTube\ThumbnailUrl;
use Song\Domain\Models\Archives\YouTube\VideoUrl;
use Song\Domain\Models\Archives\YouTube\YouTubeArchive;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongTypeId;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

class SongFactory implements SongFactoryInterface
{
    public function __construct(private readonly UuidGeneratorInterface $uuid)
    {
    }

    /**
     * @param positive-int                                                                      $orderNo
     * @param array<CreateCreatorData>                                                          $lyricists
     * @param array<CreateCreatorData>                                                          $composers
     * @param array<CreateCreatorData>                                                          $arrangers
     * @param array<CreateNonLinkArchiveData|CreateTwitterArchiveData|CreateYouTubeArchiveData> $archives
     */
    public function create(
        string $title,
        string $description,
        string $songTypeId,
        int $orderNo,
        array $lyricists,
        array $composers,
        array $arrangers,
        array $archives,
    ): Song {
        return new Song(
            new SongId($this->uuid->generate()),
            new Title($title),
            new Description($description),
            new SongTypeId($songTypeId),
            new OrderNo($orderNo),
            array_map(fn (CreateCreatorData $creator) => new Lyricist(new CreatorId($creator->creatorId), new OrderNo($creator->orderNo)), $lyricists),
            array_map(fn (CreateCreatorData $creator) => new Composer(new CreatorId($creator->creatorId), new OrderNo($creator->orderNo)), $composers),
            array_map(fn (CreateCreatorData $creator) => new Arranger(new CreatorId($creator->creatorId), new OrderNo($creator->orderNo)), $arrangers),
            array_map(fn (CreateNonLinkArchiveData|CreateTwitterArchiveData|CreateYouTubeArchiveData $archive) => match(true) {
                $archive instanceof CreateYouTubeArchiveData => $this->createYouTubeArchive($archive),
                $archive instanceof CreateTwitterArchiveData => $this->createTwitterArchive($archive),
                $archive instanceof CreateNonLinkArchiveData => $this->createNonLinkArchive($archive),
            }, $archives),
        );
    }

    private function createYouTubeArchive(CreateYouTubeArchiveData $archive): YouTubeArchive
    {
        return new YouTubeArchive(
            new ArchiveId($this->uuid->generate()),
            new ArchiveName($archive->archiveName),
            new VideoUrl($archive->videoUrl),
            new ThumbnailUrl($archive->thumbnailUrl),
            new ArchivedOn($archive->archivedOn),
            new OrderNo($archive->orderNo),
        );
    }

    private function createTwitterArchive(CreateTwitterArchiveData $archive): TwitterArchive
    {
        return new TwitterArchive(
            new ArchiveId($this->uuid->generate()),
            new ArchiveName($archive->archiveName),
            new PostUrl($archive->postUrl),
            new ArchivedOn($archive->archivedOn),
            new OrderNo($archive->orderNo),
        );
    }

    private function createNonLinkArchive(CreateNonLinkArchiveData $archive): NonLinkArchive
    {
        return new NonLinkArchive(
            new ArchiveId($this->uuid->generate()),
            new ArchivedOn($archive->archivedOn),
            new OrderNo($archive->orderNo),
        );
    }
}
