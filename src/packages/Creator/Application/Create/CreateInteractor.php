<?php

declare(strict_types=1);

namespace Creator\Application\Create;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Support\Uuid\UuidGeneratorInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly CreatorRepositoryInterface $repository,
        private readonly UuidGeneratorInterface $uuid,
    ) {
    }

    public function handle(CreateRequest $request): void
    {
        $this->repository->createCreator(new Creator(new CreatorId($this->uuid->generate()), new CreatorName($request->creatorName)));
    }
}
