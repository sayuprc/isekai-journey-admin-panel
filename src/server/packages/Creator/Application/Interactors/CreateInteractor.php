<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateOutputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly CreatorRepositoryInterface $repository,
        private readonly CreatorFactoryInterface $factory,
        private readonly CreatorNameDuplicateCheckService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        $creator = $this->factory->create($inputData->creatorName);

        if ($this->service->exists($creator->creatorName)) {
            return new Err("Creator name already exists: {$inputData->creatorName}");
        }

        $this->repository->insert($creator);

        return new Ok(new CreateOutputData($creator));
    }
}
