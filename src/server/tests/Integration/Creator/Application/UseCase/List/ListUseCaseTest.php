<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\UseCase\List;

use Creator\Application\UseCase\List\ListUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function nonEmptyCreators(): void
    {
        $uuid = $this->generateUuid();

        $this->storeCreators($this->createCreator($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(1, $response->creators);

        $this->assertSame($uuid, $response->creators[0]->creatorId->value);
        $this->assertSame('ヰ世界情緒', $response->creators[0]->name->value);
    }

    private function getInstance(): ListUseCase
    {
        $this->privilegedContext();

        return $this->app->make(ListUseCase::class);
    }
}
