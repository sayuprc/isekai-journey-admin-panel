<?php

declare(strict_types=1);

namespace Tests\Integration\Performer\DebugInfrastructures;

use Performer\DebugInfrastructures\FilePerformerRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FilePerformerRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $performer1 = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);
        $performer2 = $this->createPerformer($this->generateUuid(), '春猿火', 2);

        $this->storePerformers($performer1, $performer2);

        $performers = $this->getInstance()->all();

        $this->assertCount(2, $performers);
        $this->assertEquals([$performer1, $performer2], $performers);
    }

    #[Test]
    public function find(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storePerformers($performer);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storePerformers($performer);

        $found = $this->getInstance()->findByName($performer->name);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function save(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->getInstance()->save($performer);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNotNull($found);
        $this->assertEquals($performer, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $performer = $this->createPerformer($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storePerformers($performer);

        $this->getInstance()->delete($performer->performerId);

        $found = $this->getInstance()->find($performer->performerId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        // 空の場合は 0
        $this->assertSame(0, $repository->getMaxOrderNo());

        // データを追加
        $performer1 = $this->createPerformer($this->generateUuid(), 'performer1', 10);
        $performer2 = $this->createPerformer($this->generateUuid(), 'performer2', 30);
        $performer3 = $this->createPerformer($this->generateUuid(), 'performer3', 20);

        $this->storePerformers($performer1, $performer2, $performer3);

        // 最大値が返ることを確認
        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    private function getInstance(): FilePerformerRepository
    {
        return $this->app->make(FilePerformerRepository::class);
    }
}
