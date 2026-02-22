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
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\ValueObjects\OrderNo;

class PerformerIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly PerformerFactoryInterface $factory,
        private readonly PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Performer, DomainError>
     */
    public function prepareForCreate(string $name): Result
    {
        $result = $this->build(
            $this->generator->generate(),
            $name,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->repository->getMaxOrderNo() + 10,
        );

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $performer = $result->unwrap();

        if (! is_null($this->repository->findByName($performer->name))) {
            return new Err(new DomainRuleViolationError(PerformerName::class, sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($performer);
    }

    /**
     * @return Result<Performer, DomainError>
     */
    public function prepareForUpdate(string $performerId, string $name, int $orderNo): Result
    {
        $result = $this->build($performerId, $name, $orderNo);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $performer = $result->unwrap();

        if (! is_null($found = $this->repository->findByName($performer->name)) && ! $found->equals($performer)) {
            return new Err(new DomainRuleViolationError(PerformerName::class, sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($performer);
    }

    /**
     * @return Result<Performer, DomainError>
     */
    private function build(string $performerId, string $name, int $orderNo): Result
    {
        return Result::collect3(
            PerformerId::create($performerId),
            PerformerName::create($name),
            OrderNo::create($orderNo),
        )
            ->mapErr(function (array $errors): DomainValidationError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof DomainRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(fn (array $values): Performer => $this->factory->create(...$values));
    }
}
