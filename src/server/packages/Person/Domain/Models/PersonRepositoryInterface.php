<?php

declare(strict_types=1);

namespace Person\Domain\Models;

use Person\Domain\Criteria\PersonSearchCriteria;

interface PersonRepositoryInterface
{
    /**
     * @return array<Person>
     */
    public function all(): array;

    /**
     * @return array<Person>
     */
    public function search(PersonSearchCriteria $criteria): array;

    public function maxPage(PersonSearchCriteria $criteria): int;

    public function find(PersonId $personId): ?Person;

    public function findByName(PersonName $name): ?Person;

    public function save(Person $person): Person;

    public function getMaxOrderNo(): int;
}
