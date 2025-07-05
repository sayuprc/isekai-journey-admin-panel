<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Update\UpdateInputData;
use Creator\Application\UseCase\Update\UpdateOutputData;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;

class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private readonly CreatorRepositoryInterface $repository,
        private readonly CreatorFactoryInterface $factory,
        private readonly CreatorNameDuplicateCheckService $service,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        $creator = $this->factory->reconstitute($inputData->creatorId, $inputData->creatorName);

        if ($this->service->exists($creator->creatorName)) {
            return new Err("Creator name already exists: {$inputData->creatorName}");
        }

        $this->repository->update($creator);

        return new Ok(new UpdateOutputData($creator));
    }
}
