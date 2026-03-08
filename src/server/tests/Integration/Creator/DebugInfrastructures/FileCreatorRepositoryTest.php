<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\DebugInfrastructures;

use Creator\DebugInfrastructures\FileCreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileCreatorRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $creators = $this->getInstance()->all();

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function find(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storeCreators($creator);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storeCreators($creator);

        $found = $this->getInstance()->findByName($creator->name);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByIds(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $creators = $this->getInstance()->findByIds($creator1->creatorId, $creator2->creatorId);

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function save(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->getInstance()->save($creator);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $this->storeCreators($creator);

        $this->getInstance()->delete($creator->creatorId);

        $found = $this->getInstance()->find($creator->creatorId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $this->storeCreators($creator1, $creator2);

        $this->assertSame(20, $this->getInstance()->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): FileCreatorRepository
    {
        return $this->app->make(FileCreatorRepository::class);
    }
}
