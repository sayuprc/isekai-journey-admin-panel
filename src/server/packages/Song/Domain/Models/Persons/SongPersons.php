<?php

declare(strict_types=1);

namespace Song\Domain\Models\Persons;

use Person\Domain\Models\PersonId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SongPerson>
 */
readonly class SongPersons extends ImmutableCollection
{
    /**
     * @param list<array{personId: string, role: int, orderNo: int}> $items
     *
     * @throws DomainValidationException
     */
    public static function fromArray(array $items): self
    {
        $persons = [];
        $duplicates = [];

        foreach ($items as $item) {
            $messages = [];
            $personId = null;
            $role = null;
            $orderNo = null;

            try {
                $personId = new PersonId($item['personId']);
            } catch (InvalidDomainException $e) {
                $messages['personId'] = [$e->getMessage()];
            }

            try {
                $role = self::toRole($item['role']);
            } catch (InvalidDomainException $e) {
                $messages['role'] = [$e->getMessage()];
            }

            try {
                $orderNo = new OrderNo($item['orderNo']);
            } catch (InvalidDomainException $e) {
                $messages['orderNo'] = [$e->getMessage()];
            }

            if (is_null($personId) || is_null($role) || is_null($orderNo)) {
                throw new DomainValidationException($messages);
            }

            $person = new SongPerson($personId, $role, $orderNo);
            $key = $person->personId->value . ':' . $person->role->value;

            if (isset($duplicates[$key])) {
                throw new DomainValidationException([
                    'persons' => ['同じ人物に同じ role を重複指定できません'],
                ]);
            }

            $duplicates[$key] = true;
            $persons[] = $person;
        }

        return new self($persons);
    }

    /**
     * @param list<array{personId: string, role: int, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(static fn (array $item): SongPerson => SongPerson::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{person_id: string, role: value-of<SongPersonRole>, order_no: int}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }

    /**
     * @throws InvalidDomainException
     */
    private static function toRole(int $role): SongPersonRole
    {
        return SongPersonRole::tryFrom($role) ?? throw new InvalidDomainException("不正な role です: {$role}");
    }
}
