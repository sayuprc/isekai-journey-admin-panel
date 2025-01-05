<?php

declare(strict_types=1);

namespace App\Features\Song\Infrastructures\Repositories;

use App\Features\Song\Domain\Entities\Description;
use App\Features\Song\Domain\Entities\OrderNo;
use App\Features\Song\Domain\Entities\ReleasedOn;
use App\Features\Song\Domain\Entities\Song;
use App\Features\Song\Domain\Entities\SongId;
use App\Features\Song\Domain\Entities\SongLink;
use App\Features\Song\Domain\Entities\SongLinkId;
use App\Features\Song\Domain\Entities\SongLinkName;
use App\Features\Song\Domain\Entities\Title;
use App\Features\Song\Domain\Entities\Url;
use App\Features\Song\Domain\Repositories\SongRepositoryInterface;
use App\Features\SongType\Domain\Entities\SongTypeId;
use App\Shared\Exceptions\APIException;
use DateTimeImmutable;
use Exception;
use Generated\IsekaiJourney\Shared\Status;
use Generated\IsekaiJourney\Song\ListSongsRequest;
use Generated\IsekaiJourney\Song\ListSongsResponse;
use Generated\IsekaiJourney\Song\Song as GrpcSong;
use Generated\IsekaiJourney\Song\SongLink as GrpcSongLink;
use Generated\IsekaiJourney\Song\SongServiceClient;
use stdClass;

use const Grpc\STATUS_OK;

class SongRepository implements SongRepositoryInterface
{
    public function __construct(private readonly SongServiceClient $client)
    {
    }

    public function listSongs(): array
    {
        [$response, $status] = $this->client->ListSongs(new ListSongsRequest())->wait();

        assert($response instanceof ListSongsResponse);
        assert($status instanceof stdClass);

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
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
