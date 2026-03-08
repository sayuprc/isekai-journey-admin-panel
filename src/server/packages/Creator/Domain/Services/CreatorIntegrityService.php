<?php

declare(strict_types=1);

namespace Creator\Domain\Services;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

class CreatorIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly CreatorFactoryInterface $factory,
        private readonly CreatorRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Creator, DomainError>
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

        $creator = $result->unwrap();

        if (! is_null($this->repository->findByName($creator->name))) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($creator);
    }

    /**
     * @return Result<Creator, DomainError>
     */
    public function prepareForUpdate(string $creatorId, string $name, int $orderNo): Result
    {
        $result = $this->build($creatorId, $name, $orderNo);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $creator = $result->unwrap();

        if (! is_null($found = $this->repository->findByName($creator->name)) && ! $found->equals($creator)) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($creator);
    }

    /**
     * @return Result<Creator, DomainError>
     */
    private function build(string $creatorId, string $name, int $orderNo): Result
    {
        return Result::collect3(
            CreatorId::create($creatorId),
            CreatorName::create($name),
            OrderNo::create($orderNo),
        )
            ->mapErr(function (array $errors): DomainValidationError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(fn (array $values): Creator => $this->factory->create(...$values));
    }
}
