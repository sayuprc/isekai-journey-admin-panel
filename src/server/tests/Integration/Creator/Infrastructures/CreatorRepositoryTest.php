<?php

declare(strict_types=1);

namespace Tests\Integration\Creator\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Creator\Infrastructures\CreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreatorRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $creators = $repository->all();

        $this->assertCount(2, $creators);
        $this->assertEquals([$creator1, $creator2], $creators);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $found = $repository->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(CreatorId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $found = $repository->findByName($creator->name);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function findByIds(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $creators = $repository->findByIds($creator1->creatorId, $creator2->creatorId);

        $this->assertCount(2, $creators);
        $this->assertEqualsCanonicalizing([$creator1, $creator2], $creators);
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $found = $repository->find($creator->creatorId);

        $this->assertNotNull($found);
        $this->assertEquals($creator, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $creator = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 1);

        $repository->save($creator);

        $repository->delete($creator->creatorId);

        $found = $repository->find($creator->creatorId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $creator1 = $this->createCreator($this->generateUuid(), 'ヰ世界情緒', 10);
        $creator2 = $this->createCreator($this->generateUuid(), '香椎モイミ', 20);

        $repository->save($creator1);
        $repository->save($creator2);

        $this->assertSame(20, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): CreatorRepository
    {
        return $this->app->make(CreatorRepository::class);
    }
}
