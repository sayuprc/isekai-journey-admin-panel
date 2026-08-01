<?php

declare(strict_types=1);

namespace Person\Domain\Services;

use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Validation\Field;
use Support\Domain\Validation\Fields;
use Support\Domain\ValueObjects\OrderNo;

class PersonIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly PersonRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws DomainValidationException
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(string $name): Person
    {
        $person = $this->build(
            $this->generator->generate(),
            $name,
            $this->repository->getMaxOrderNo() + 10,
        );

        if (! is_null($this->repository->findByName($person->name))) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $person;
    }

    /**
     * @throws DomainValidationException
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(string $personId, string $name, int $orderNo): Person
    {
        $person = $this->build($personId, $name, $orderNo);

        if (! is_null($found = $this->repository->findByName($person->name)) && ! $found->equals($person)) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $person;
    }

    private function build(string $personId, string $name, int $orderNo): Person
    {
        $personIdField = Field::of('personId', static fn (): PersonId => new PersonId($personId));
        $nameField = Field::of('name', static fn (): PersonName => new PersonName($name));
        $orderNoField = Field::of('orderNo', static fn (): OrderNo => new OrderNo($orderNo));
        Fields::validate($personIdField, $nameField, $orderNoField);

        return new Person($personIdField->value(), $nameField->value(), $orderNoField->value());
    }
}
