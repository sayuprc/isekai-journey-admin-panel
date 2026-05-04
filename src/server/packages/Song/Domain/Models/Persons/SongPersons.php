<?php

declare(strict_types=1);

namespace Song\Domain\Models\Persons;

use Person\Domain\Models\PersonId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SongPerson>
 */
readonly class SongPersons extends ImmutableCollection
{
    /**
     * @param list<array{personId: string, role: string, orderNo: int}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $persons = [];
        $duplicates = [];

        foreach ($items as $item) {
            $result = Result::collect3(
                PersonId::create($item['personId']),
                self::toRole($item['role']),
                OrderNo::create($item['orderNo']),
            )->map(fn (array $values) => new SongPerson(...$values));

            if ($result->isErr()) {
                $messages = [];
                foreach ($result->unwrapErr() as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new Err(new DomainValidationError($messages));
            }

            $person = $result->unwrap();
            $key = $person->personId->value . ':' . $person->role->value;

            if (isset($duplicates[$key])) {
                return new Err(new DomainValidationError([
                    'persons' => ['同じ人物に同じ role を重複指定できません'],
                ]));
            }

            $duplicates[$key] = true;
            $persons[] = $person;
        }

        return new Ok(new self($persons));
    }

    /**
     * @param list<array{personId: string, role: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(fn (array $item): SongPerson => SongPerson::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{person_id: string, role: value-of<SongPersonRole>, order_no: int}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(fn (SongPerson $item): array => $item->toArray())
            ->toList();
    }

    /**
     * @return Result<SongPersonRole, EntityRuleViolationError>
     */
    private static function toRole(string $role): Result
    {
        $found = SongPersonRole::tryFrom($role);

        if (is_null($found)) {
            return new Err(new EntityRuleViolationError('role', "不正な role です: {$role}"));
        }

        return new Ok($found);
    }
}
