<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\UseCase\List;

use Performer\Application\UseCase\List\ListUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function nonEmptyPerformers(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(1, $response->performers);

        $this->assertSame($uuid, $response->performers[0]->performerId->value);
        $this->assertSame('ヰ世界情緒', $response->performers[0]->name->value);
        $this->assertSame(1, $response->performers[0]->orderNo->value);
    }

    private function getInstance(): ListUseCase
    {
        $this->privilegedContext();

        return $this->app->make(ListUseCase::class);
    }
}
