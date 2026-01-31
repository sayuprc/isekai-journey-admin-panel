<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateOutputData;
use Performer\Application\UseCase\Create\CreateUseCaseInterface;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Performer\Domain\Services\PerformerNameDuplicateCheckService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private PerformerRepositoryInterface $repository,
        private PerformerFactoryInterface $factory,
        private PerformerNameDuplicateCheckService $service,
        private UuidGeneratorInterface $generator,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return Result::collect3(
            PerformerId::create($this->generator->generate()),
            PerformerName::create($inputData->performerName),
            OrderNo::create($inputData->orderNo),
        )
            // TODO エラーハンドリング強化
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): Performer => $this->factory->create(...$values))
            ->andThen(function (Performer $performer): Result {
                return $this->transaction->scope(function () use ($performer): Result {
                    if ($this->service->exists($performer->performerName)) {
                        return new Err("Performer name already exists: {$performer->performerName->value}");
                    }

                    $this->repository->save($performer);

                    return new Ok(new CreateOutputData($performer));
                });
            });
    }
}
