<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Application\Interactors;

use Creator\Application\Interactors\ListInteractor;
use Creator\Application\UseCase\List\ListOutputData;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function emptyCreators(): void
    {
        $response = $this->getInstance()->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->creators);
    }

    #[Test]
    public function nonEmptyCreators(): void
    {
        $uuid = $this->generateUuid();
        $this->factory(
            FileCreatorRepository::class,
            $uuid,
            new Creator(new CreatorId($uuid), new CreatorName('ヰ世界情緒'))
        );

        $response = $this->getInstance()->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(1, $response->creators);

        $this->assertSame($uuid, $response->creators[0]->creatorId->value);
        $this->assertSame('ヰ世界情緒', $response->creators[0]->creatorName->value);
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
