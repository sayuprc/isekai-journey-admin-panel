<?php

declare(strict_types=1);

namespace SongType\Application\Delete;

use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\Delete\DeleteRequest;
use SongType\UseCases\Delete\DeleteUseCaseInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    public function handle(DeleteRequest $request): void
    {
        $this->repository->delete(new SongTypeId($request->songTypeId));
    }
}
