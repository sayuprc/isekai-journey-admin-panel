<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\UseCase\Get;

use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetUseCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function getPerformer(): void
    {
        $uuid = $this->generateUuid();

        $this->storePerformers($this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($uuid, $response->performer->performerId->value);
        $this->assertSame('ヰ世界情緒', $response->performer->name->value);
        $this->assertSame(1, $response->performer->orderNo->value);
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
