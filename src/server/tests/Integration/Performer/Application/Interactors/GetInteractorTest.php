<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\GetInteractor;
use Performer\Application\UseCase\Get\GetInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function getPerformer(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $uuid, $this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($uuid, $response->performer->performerId->value);
        $this->assertSame('ヰ世界情緒', $response->performer->performerName->value);
        $this->assertSame(1, $response->performer->orderNo->value);
    }

    private function getInstance(): GetInteractor
    {
        return $this->app->make(GetInteractor::class);
    }
}
