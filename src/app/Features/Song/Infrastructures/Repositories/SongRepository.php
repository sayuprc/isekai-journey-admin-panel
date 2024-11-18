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
        /**
         * @var ListSongsResponse $response
         * @var stdClass          $status
         */
        [$response, $status] = $this->client->ListSongs(new ListSongsRequest())->wait();

        if ($status->code !== STATUS_OK) {
            throw new APIException("API Execution Errors: {$status->details}", $status->code);
        }

        if ($response->getStatus() !== Status::SUCCESS) {
            throw new Exception("Response Errors: [{$response->getStatus()}] {$response->getMessage()}");
        }

        $songs = [];

        /** @var GrpcSong $song */
        foreach ($response->getSongs() as $song) {
            $songs[] = $this->toSong($song);
        }

        return $songs;
    }

    private function toSong(GrpcSong $song): Song
    {
        $songLinks = [];

        /** @var GrpcSongLink $songLink */
        foreach ($song->getSongLinks() as $songLink) {
            $songLinks[] = $this->toSongLink($songLink);
        }

        return new Song(
            new SongId($song->getSongId()),
            new Title($song->getTitle()),
            new Description($song->getDescription()),
            new ReleasedOn(
                new DateTimeImmutable(
                    sprintf(
                        '%04s-%02s-%02s',
                        $song->getReleasedOn()->getYear(),
                        $song->getReleasedOn()->getMonth(),
                        $song->getReleasedOn()->getDay(),
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
