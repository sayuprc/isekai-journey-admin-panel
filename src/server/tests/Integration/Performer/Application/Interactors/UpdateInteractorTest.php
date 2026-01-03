<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Application\Interactors;

use Performer\Application\Interactors\UpdateInteractor;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FilePerformerRepository::class,
            $uuid,
            new Performer(new PerformerId($uuid), new PerformerName('共演者'), new OrderNo(1)),
        );

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'ヰ世界情緒', 2));

        $this->assertTrue($result->isOk());

        /** @var array<Performer> $performers */
        $performers = $this->getAll(FilePerformerRepository::class);
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', $performers[$uuid]->performerName->value);
        $this->assertSame(2, $performers[$uuid]->orderNo->value);
    }

    #[Test]
    public function updateFailsIfNameAlreadyExists(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FilePerformerRepository::class,
            $uuid,
            new Performer(new PerformerId($uuid), new PerformerName('ヰ世界情緒'), new OrderNo(1)),
        );

        $result = $this->getInstance()->handle(new UpdateInputData($this->generateUuid(), 'ヰ世界情緒', 2));

        $this->assertTrue($result->isErr());

        /** @var array<Performer> $performers */
        $performers = $this->getAll(FilePerformerRepository::class);
        $this->assertCount(1, $performers);
        $this->assertSame('ヰ世界情緒', $performers[$uuid]->performerName->value);
        $this->assertSame(1, $performers[$uuid]->orderNo->value);
    }

    private function getInstance(): UpdateInteractor
    {
        return $this->app->make(UpdateInteractor::class);
    }
}
