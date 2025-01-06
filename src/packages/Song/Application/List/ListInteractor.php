<?php

declare(strict_types=1);

namespace Song\Application\List;

use Song\Domain\Repositories\SongRepositoryInterface;
use Song\UseCases\List\ListResponse;
use Song\UseCases\List\ListUseCaseInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly SongRepositoryInterface $client)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->client->listSongs());
    }
}
