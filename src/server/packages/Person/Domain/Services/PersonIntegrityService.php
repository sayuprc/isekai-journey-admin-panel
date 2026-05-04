<?php

declare(strict_types=1);

namespace Person\Domain\Services;

use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Person\Domain\Models\PersonRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

class PersonIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly PersonRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Person, DomainError>
     */
    public function prepareForCreate(string $name): Result
    {
        $result = $this->build(
            $this->generator->generate(),
            $name,
            $this->repository->getMaxOrderNo() + 10,
        );

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $person = $result->unwrap();

        if (! is_null($this->repository->findByName($person->name))) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($person);
    }

    /**
     * @return Result<Person, DomainError>
     */
    public function prepareForUpdate(string $personId, string $name, int $orderNo): Result
    {
        $result = $this->build($personId, $name, $orderNo);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $person = $result->unwrap();

        if (! is_null($found = $this->repository->findByName($person->name)) && ! $found->equals($person)) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($person);
    }

    /**
     * @return Result<Person, DomainError>
     */
    private function build(string $personId, string $name, int $orderNo): Result
    {
        return Result::collect3(
            PersonId::create($personId),
            PersonName::create($name),
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
            ->map(fn (array $values): Person => new Person(...$values));
    }
}
