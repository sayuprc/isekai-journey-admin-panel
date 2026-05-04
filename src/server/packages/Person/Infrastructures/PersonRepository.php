<?php

declare(strict_types=1);

namespace Person\Infrastructures;

use App\Models\Person\Person as ModelsPerson;
use Override;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonName;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class PersonRepository implements PersonRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
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
