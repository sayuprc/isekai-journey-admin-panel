<?php

declare(strict_types=1);

namespace Creator\Application\Create;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use Creator\UseCases\Create\CreateInputData;
use Creator\UseCases\Create\CreateUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

class CreateInteractor implements CreateUseCaseInterface
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
    public function handle(CreateInputData $inputData): Result
    {
        $creator = $this->factory->create($inputData->creatorName);

        if ($this->service->exists($creator->creatorName)) {
            return new Err("Creator name already exists: {$inputData->creatorName}");
        }

        $this->repository->insert($creator);

        return new Ok(null);
    }
}
