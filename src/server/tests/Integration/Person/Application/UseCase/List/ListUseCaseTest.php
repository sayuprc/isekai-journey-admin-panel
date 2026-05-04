<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\UseCase\List;

use Person\Application\UseCase\List\ListUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function list(): void
    {
        $person = $this->createPerson($this->generateUuid(), 'ヰ世界情緒', 1);
        $this->storePersons($person);

        $result = $this->getInstance()->handle();

        $this->assertTrue($result->isOk());
        $this->assertEquals([$person], $result->unwrap()->persons);
    }

    private function getInstance(): ListUseCase
    {
        $this->privilegedContext();

        return $this->app->make(ListUseCase::class);
    }
}
