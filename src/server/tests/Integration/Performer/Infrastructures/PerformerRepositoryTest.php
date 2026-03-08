<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\Infrastructures;

use Performer\Domain\Models\PerformerId;
use Performer\Infrastructures\PerformerRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class PerformerRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 2);

        $repository->save($performer1);
        $repository->save($performer2);

        $performers = $repository->all();

        $this->assertCount(2, $performers);
        $this->assertEquals([$performer1, $performer2], $performers);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $found = $repository->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(PerformerId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $found = $repository->findByName($performer->name);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $found = $repository->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($performer);

        $repository->delete($performer->performerId);

        $found = $repository->find($performer->performerId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $performer1 = $this->createPerformer($this->generateUuid(), 'performer1', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'performer2', 30);
        $performer3 = $this->createPerformer($this->generateUuid(), 'performer3', 20);

        $repository->save($performer1);
        $repository->save($performer2);
        $repository->save($performer3);

        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): PerformerRepository
    {
        return $this->app->make(PerformerRepository::class);
    }
}
