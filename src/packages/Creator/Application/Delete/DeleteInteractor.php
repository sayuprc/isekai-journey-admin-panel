<?php

declare(strict_types=1);

namespace Creator\Application\Delete;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Delete\DeleteRequest;
use Creator\UseCases\Delete\DeleteUseCaseInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    public function handle(DeleteRequest $request): void
    {
        $this->repository->delete(new CreatorId($request->creatorId));
    }
}
