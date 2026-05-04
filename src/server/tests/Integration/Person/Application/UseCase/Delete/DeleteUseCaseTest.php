<?php

declare(strict_types=1);

namespace Tests\Integration\Person\Application\UseCase\Delete;

use App\Models\Person\Person as ModelsPerson;
use Person\Application\UseCase\Delete\DeleteInputData;
use Person\Application\UseCase\Delete\DeleteUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storePersons($this->createPerson($uuid, '人物', 1));

        $result = $this->getInstance()->handle(new DeleteInputData($uuid));

        $this->assertTrue($result->isOk());
        $this->assertCount(0, ModelsPerson::query()->get()->all());
    }

    private function getInstance(): DeleteUseCase
    {
        $this->privilegedContext();

        return $this->app->make(DeleteUseCase::class);
    }
}
