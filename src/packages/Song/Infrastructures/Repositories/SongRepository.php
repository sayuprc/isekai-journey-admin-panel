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
use Shared\Exceptions\APIException;
use Shared\Grpc\Status;
use Shared\Mapper\MapperInterface;
use Song\Domain\Entities\Description;
use Song\Domain\Entities\OrderNo;
use Song\Domain\Entities\ReleasedOn;
use Song\Domain\Entities\Song;
use Song\Domain\Entities\SongId;
use Song\Domain\Entities\SongLink;
use Song\Domain\Entities\SongLinkId;
use Song\Domain\Entities\SongLinkName;
use Song\Domain\Entities\SongTypeId;
use Song\Domain\Entities\Title;
use Song\Domain\Entities\Url;
use Song\Domain\Repositories\SongRepositoryInterface;

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
