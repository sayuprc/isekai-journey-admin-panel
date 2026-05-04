<?php

declare(strict_types=1);

namespace Person\Domain\Models;

interface PersonRepositoryInterface
{
    public function findByName(PersonName $name): ?Person;

    public function save(Person $person): Person;

    public function getMaxOrderNo(): int;
}
