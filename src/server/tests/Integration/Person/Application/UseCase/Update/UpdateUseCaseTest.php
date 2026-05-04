<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\UseCase\Update;

use Person\Application\UseCase\Update\UpdateInputData;
use Person\Application\UseCase\Update\UpdateUseCase;
use Person\Domain\Models\PersonRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $personId = $this->generateUuid();

        $this->storePersons($this->createPerson($personId, '人物', 10));

        $result = $this->getInstance()->handle(new UpdateInputData($personId, 'ヰ世界情緒', 20));

        $this->assertTrue($result->isOk());

        $persons = $this->app->make(PersonRepositoryInterface::class)->all();
        $this->assertCount(1, $persons);
        $this->assertSame('ヰ世界情緒', array_first($persons)->name->value);
        $this->assertSame(20, array_first($persons)->orderNo->value);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
