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
        $result = Result::collect3(
            PersonId::create($this->generator->generate()),
            PersonName::create($name),
            OrderNo::create($this->repository->getMaxOrderNo() + 10),
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

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $person = $result->unwrap();

        if (! is_null($this->repository->findByName($person->name))) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($person);
    }
}
