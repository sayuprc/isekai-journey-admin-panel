<?php

declare(strict_types=1);

namespace Creator\Application\Edit;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use Creator\UseCases\Edit\EditInputData;
use Creator\UseCases\Edit\EditUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

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
