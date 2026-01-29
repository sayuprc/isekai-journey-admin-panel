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

        $this->factory(FileCreatorRepository::class, $uuid, $this->createCreator($uuid, 'ヰ世界情緒'));

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($uuid, $response->creator->creatorId->value);
        $this->assertSame('ヰ世界情緒', $response->creator->creatorName->value);
    }

    #[Test]
    public function failureGetCreator(): void
    {
        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertTrue($result->isErr());

        $this->assertSame('Creator not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }

    private function getInstance(): GetInteractor
    {
        return $this->app->make(GetInteractor::class);
    }
}
