<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Edit\EditInputData;
use Creator\Application\UseCase\Edit\EditUseCaseInterface;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;

class EditInteractor implements EditUseCaseInterface
{
    public function __construct(
        private readonly CreatorRepositoryInterface $repository,
        private readonly CreatorFactoryInterface $factory,
        private readonly CreatorNameDuplicateCheckService $service,
    ) {
    }

    /**
     * @return Result<null, string>
     */
    public function handle(EditInputData $inputData): Result
    {
        $creator = $this->factory->reconstitute($inputData->creatorId, $inputData->creatorName);

        if ($this->service->exists($creator->creatorName)) {
            return new Err("Creator name already exists: {$inputData->creatorName}");
        }

        $this->repository->update($creator);

        return new Ok(null);
    }
}
