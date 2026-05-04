<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Infrastructures;

use Person\Infrastructures\PersonRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class PersonRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $person = $this->createPerson($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($person);

        $found = $repository->findByName($person->name);

        $this->assertNotNull($found);
        $this->assertEquals($person, $found);
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): PersonRepository
    {
        return $this->app->make(PersonRepository::class);
    }
}
