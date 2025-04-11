<?php

declare(strict_types=1);

namespace Song\Infrastructures\Repositories;

use DateTimeImmutable;
use Exception;
use Generated\IsekaiJourney\Shared\Status as GrpcStatus;
use Generated\IsekaiJourney\Song\ListSongsRequest;
use Generated\IsekaiJourney\Song\ListSongsResponse;
use Generated\IsekaiJourney\Song\Song as GrpcSong;
use Generated\IsekaiJourney\Song\SongLink as GrpcSongLink;
use Generated\IsekaiJourney\Song\SongServiceClient;
use Song\Domain\Models\Description;
use Song\Domain\Models\ReleasedOn;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongLink;
use Song\Domain\Models\SongLinkId;
use Song\Domain\Models\SongLinkName;
use Song\Domain\Models\Title;
use Song\Domain\Models\Url;
use Song\Domain\Repositories\SongRepositoryInterface;
use SongType\Domain\Models\SongTypeId;
use Support\Domain\ValueObjects\OrderNo;
use Support\Exceptions\APIException;
use Support\Grpc\Status;
use Support\Mapper\MapperInterface;

class SongRepository implements SongRepositoryInterface
{
    public function __construct(
        private readonly SongServiceClient $client,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function listSongs(): array
    {
        [$response, $status] = $this->client->ListSongs(new ListSongsRequest())->wait();
        $status = $this->mapper->map(Status::class, $status);

        assert($response instanceof ListSongsResponse);

        if (! $status->isOk()) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== GrpcStatus::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $songs = [];

        foreach ($response->getSongs() as $song) {
            assert($song instanceof GrpcSong);
            $songs[] = $this->toSong($song);
        }

        return $songs;
    }

    private function toSong(GrpcSong $song): Song
    {
        $songLinks = [];

        foreach ($song->getSongLinks() as $songLink) {
            assert($songLink instanceof GrpcSongLink);
            $songLinks[] = $this->toSongLink($songLink);
        }

        $grpcReleasedOn = $song->getReleasedOn();
        assert(! is_null($grpcReleasedOn));

        return new Song(
            new SongId($song->getSongId()),
            new Title($song->getTitle()),
            new Description($song->getDescription()),
            new ReleasedOn(
                new DateTimeImmutable(
                    sprintf(
                        '%04s-%02s-%02s',
                        $grpcReleasedOn->getYear(),
                        $grpcReleasedOn->getMonth(),
                        $grpcReleasedOn->getDay(),
                    )
                )
            ),
            new SongTypeId($song->getSongTypeId()),
            new OrderNo($song->getOrderNo()),
            $songLinks,
        );
    }

    private function toSongLink(GrpcSongLink $songLink): SongLink
    {
        return new SongLink(
            new SongLinkId($songLink->getSongLinkId()),
            new SongLinkName($songLink->getSongLinkName()),
            new Url($songLink->getUrl()),
            new OrderNo($songLink->getOrderNo()),
        );
    }
}
