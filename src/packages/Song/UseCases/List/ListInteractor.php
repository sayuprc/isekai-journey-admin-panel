<?php

declare(strict_types=1);

namespace Song\UseCases\List;

use Song\Domain\Repositories\SongRepositoryInterface;

class ListInteractor
{
    public function __construct(private readonly SongRepositoryInterface $client)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->client->listSongs());
    }
}
