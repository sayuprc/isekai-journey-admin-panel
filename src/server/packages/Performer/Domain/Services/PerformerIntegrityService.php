<?php

declare(strict_types=1);

namespace Performer\Domain\Services;

use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

/**
 * TODO エラーハンドリングを強化する
 */
class PerformerIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly PerformerFactoryInterface $factory,
        private readonly PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Performer, string>
     */
    public function prepareForCreate(string $performerName): Result
    {
        $result = $this->build(
            $this->generator->generate(),
            $performerName,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->repository->getMaxOrderNo() + 10,
        );

        if ($result->isErr()) {
            return new Err('');
        }

        $performer = $result->unwrap();

        if (! is_null($this->repository->findByName($performer->performerName))) {
            return new Err(sprintf('すでに使われている名前です "%s"', $performerName));
        }

        return new Ok($performer);
    }

    /**
     * @return Result<Performer, string>
     */
    public function prepareForUpdate(string $performerId, string $performerName, int $orderNo): Result
    {
        $result = $this->build($performerId, $performerName, $orderNo);

        if ($result->isErr()) {
            return new Err('');
        }

        $performer = $result->unwrap();

        if (
            ! is_null($found = $this->repository->findByName($performer->performerName))
            && $found->performerId->value !== $performer->performerId->value
        ) {
            return new Err(sprintf('すでに使われている名前です "%s"', $performerName));
        }

        return new Ok($performer);
    }

    /**
     * @return Result<Performer, string>
     */
    private function build(string $performerId, string $performerName, int $orderNo): Result
    {
        return Result::collect3(
            PerformerId::create($performerId),
            PerformerName::create($performerName),
            OrderNo::create($orderNo),
        )
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): Performer => $this->factory->create(...$values));
    }
}
