<?php

declare(strict_types=1);

namespace Person\Infrastructures;

use App\Models\Person\Person as ModelsPerson;
use Override;
use Person\Domain\Criteria\PersonSearchCriteria;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

readonly class PersonRepository implements PersonRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function all(): array
    {
        return ModelsPerson::query()
            ->orderBy('order_no')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function search(PersonSearchCriteria $criteria): array
    {
        $query = ModelsPerson::query();

        if ($criteria->name->isPresent()) {
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $query->orderBy($criteria->sort->value, $criteria->order->value)
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function maxPage(PersonSearchCriteria $criteria): int
    {
        $query = ModelsPerson::query();

        if ($criteria->name->isPresent()) {
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        return (int)ceil($query->count() / $criteria->perPage->value);
    }

    #[Override]
    public function find(PersonId $personId): ?Person
    {
        $found = ModelsPerson::query()
            ->where('person_id', $this->converter->toBin($personId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function findByIds(PersonId ...$personIds): array
    {
        return ModelsPerson::query()
            ->whereIn(
                'person_id',
                array_map(fn (PersonId $personId): string => $this->converter->toBin($personId->value), $personIds),
            )
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function findByName(PersonName $name): ?Person
    {
        $found = ModelsPerson::query()
            ->where('name', $name->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function save(Person $person): Person
    {
        ModelsPerson::query()->upsert(
            [
                ...$person->toArray(),
                'person_id' => $this->converter->toBin($person->personId->value),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['person_id'],
            [
                'name',
                'order_no',
                'updated_at',
            ],
        );

        return $person;
    }

    #[Override]
    public function delete(PersonId $personId): void
    {
        ModelsPerson::query()->where('person_id', $this->converter->toBin($personId->value))->delete();
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsPerson::query()->max('order_no') ?? 0;
    }

    private function hydrate(ModelsPerson $row): Person
    {
        return Person::reconstruct(
            $this->converter->toUuid($row->person_id),
            $row->name,
            $row->order_no,
        );
    }
}
