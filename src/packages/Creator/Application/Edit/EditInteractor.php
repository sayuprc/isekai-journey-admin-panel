<?php

declare(strict_types=1);

namespace Creator\Application\Edit;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Edit\EditRequest;
use Creator\UseCases\Edit\EditUseCaseInterface;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly CreatorRepositoryInterface $repository,
        private readonly CreatorFactoryInterface $factory,
    ) {
    }

    public function handle(EditRequest $request): void
    {
        $creator = $this->factory->reconstitute($request->creatorId, $request->creatorName);

        $this->repository->update($creator);
    }
}
