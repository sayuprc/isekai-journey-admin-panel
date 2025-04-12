<?php

declare(strict_types=1);

namespace Song\Infrastructures\Repositories;

use Exception;
use Generated\IsekaiJourney\Shared\Status as GrpcStatus;
use Generated\IsekaiJourney\Song\ListSongsRequest;
use Generated\IsekaiJourney\Song\ListSongsResponse;
use Generated\IsekaiJourney\Song\Song as GrpcSong;
use Generated\IsekaiJourney\Song\SongServiceClient;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
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
        $grpcReleasedOn = $song->getReleasedOn();
        assert(! is_null($grpcReleasedOn));

        return new Song(
            new SongId($song->getSongId()),
            new Title($song->getTitle()),
            new Description($song->getDescription()),
            new SongTypeId($song->getSongTypeId()),
            [],
            [],
            [],
            [],
            new OrderNo($song->getOrderNo()),
        );
    }
}
