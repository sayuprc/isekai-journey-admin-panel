<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\UpdateInteractor;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(FilePerformerRepository::class, $uuid, $this->createPerformer($uuid, '共演者', 1));

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'ヰ世界情緒', 2));

        $this->assertTrue($result->isOk());

        /** @var array<Performer> $performers */
        $performers = $this->getAll(FilePerformerRepository::class);
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', $performers[$uuid]->performerName->value);
        $this->assertSame(2, $performers[$uuid]->orderNo->value);
    }

    private function getInstance(): UpdateInteractor
    {
        return $this->app->make(UpdateInteractor::class);
    }
}
