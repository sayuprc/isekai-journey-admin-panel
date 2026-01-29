<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\ListInteractor;
use Performer\Application\UseCase\List\ListOutputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function emptyPerformers(): void
    {
        $response = $this->getInstance()->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->performers);
    }

    #[Test]
    public function nonEmptyPerformers(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $uuid, $this->createPerformer($uuid, 'ヰ世界情緒', 1));

        $response = $this->getInstance()->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(1, $response->performers);

        $this->assertSame($uuid, $response->performers[0]->performerId->value);
        $this->assertSame('ヰ世界情緒', $response->performers[0]->performerName->value);
        $this->assertSame(1, $response->performers[0]->orderNo->value);
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
