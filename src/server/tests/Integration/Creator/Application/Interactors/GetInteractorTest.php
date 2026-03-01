<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\GetInteractor;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function getCreator(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $this->createCreator($uuid, 'ヰ世界情緒', 1)->toArray());

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($uuid, $response->creator->creatorId->value);
        $this->assertSame('ヰ世界情緒', $response->creator->name->value);
    }

    private function getInstance(): GetInteractor
    {
        $this->privilegedContext();

        return $this->app->make(GetInteractor::class);
    }
}
