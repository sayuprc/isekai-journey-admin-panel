<?php

declare(strict_types=1);

namespace Creator\Application\Create;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Create\CreateRequest;
use Creator\UseCases\Create\CreateUseCaseInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly CreatorRepositoryInterface $repository,
        private readonly CreatorFactoryInterface $factory,
    ) {
    }

    public function handle(CreateRequest $request): void
    {
        $this->repository->insert($this->factory->create($request->creatorName));
    }
}
