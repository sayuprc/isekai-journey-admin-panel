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

    /**
     * @return array<Person>
     */
    public function findByIds(PersonId ...$personIds): array;

    public function findByName(PersonName $name): ?Person;

    public function save(Person $person): Person;

    public function delete(PersonId $personId): void;

    public function getMaxOrderNo(): int;

    /**
     * 現行 order_no 昇順（同値は person_id 昇順）を保ったまま 10 刻みで振り直す
     *
     * @return list<array{id: string, order_no: int}>
     */
    public function resetOrderNumbers(): array;
}
