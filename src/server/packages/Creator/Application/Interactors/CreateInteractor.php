<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Create\CreateInputData;
use Creator\Application\UseCase\Create\CreateOutputData;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private CreatorRepositoryInterface $repository,
        private CreatorFactoryInterface $factory,
        private CreatorNameDuplicateCheckService $service,
        private UuidGeneratorInterface $generator,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return Result::collect(
            CreatorId::create($this->generator->generate()),
            CreatorName::create($inputData->creatorName),
        )
            // TODO エラーハンドリング強化
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): Creator => $this->factory->create(...$values))
            ->andThen(function (Creator $creator): Result {
                return $this->transaction->scope(function () use ($creator): Result {
                    if ($this->service->exists($creator->creatorName)) {
                        return new Err("Creator name already exists: {$creator->creatorName->value}");
                    }

                    $this->repository->save($creator);

                    return new Ok(new CreateOutputData($creator));
                });
            });
    }
}
