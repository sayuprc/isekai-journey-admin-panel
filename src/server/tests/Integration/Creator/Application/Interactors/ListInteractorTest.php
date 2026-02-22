<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\ListInteractor;
use Creator\DebugInfrastructures\FileCreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function nonEmptyCreators(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FileCreatorRepository::class, $this->createCreator($uuid, 'ヰ世界情緒')->toArray());

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(1, $response->creators);

        $this->assertSame($uuid, $response->creators[0]->creatorId->value);
        $this->assertSame('ヰ世界情緒', $response->creators[0]->name->value);
    }

    private function getInstance(): ListInteractor
    {
        $this->privilegedContext();

        return $this->app->make(ListInteractor::class);
    }
}
