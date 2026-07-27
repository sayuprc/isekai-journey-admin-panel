<?php

declare(strict_types=1);

namespace Tests\Integration\Support\Infrastructures\Database;

use Person\Domain\Models\PersonId;
use Person\Infrastructures\PersonRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class OrderNoResetterTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function resetsInStepsOfTenPreservingRelativeOrder(): void
    {
        $repository = $this->app->make(PersonRepository::class);

        $first = $this->generateUuid();
        $second = $this->generateUuid();
        $third = $this->generateUuid();

        $repository->save($this->createPerson($first, 'A', 2));
        $repository->save($this->createPerson($second, 'B', 5));
        $repository->save($this->createPerson($third, 'C', 50));

        $updated = $repository->resetOrderNumbers();

        $this->assertCount(3, $updated);
        $this->assertSame(10, $repository->find(PersonId::reconstruct($first))?->orderNo->value);
        $this->assertSame(20, $repository->find(PersonId::reconstruct($second))?->orderNo->value);
        $this->assertSame(30, $repository->find(PersonId::reconstruct($third))?->orderNo->value);
    }

    #[Test]
    public function skipsUnchangedRows(): void
    {
        $repository = $this->app->make(PersonRepository::class);

        $repository->save($this->createPerson($this->generateUuid(), 'A', 10));
        $repository->save($this->createPerson($this->generateUuid(), 'B', 20));

        $this->assertSame([], $repository->resetOrderNumbers());
    }
}
