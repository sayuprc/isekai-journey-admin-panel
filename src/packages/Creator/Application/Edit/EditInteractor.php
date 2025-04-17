<?php

declare(strict_types=1);

namespace Creator\Application\Edit;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Edit\EditRequest;
use Creator\UseCases\Edit\EditUseCaseInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    public function handle(EditRequest $request): void
    {
        $creator = new Creator(
            new CreatorId($request->creatorId),
            new CreatorName($request->creatorName),
        );

        $this->repository->editCreator($creator);
    }
}
