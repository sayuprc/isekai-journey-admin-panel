<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\DatabaseTestCase;

class SongTagRepositoryTest extends DatabaseTestCase
{
    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $tag = $this->createSongTag($this->generateUuid(), 'ロック', 10);

        $repository->save($tag);

        $found = $repository->findByName($tag->name);

        $this->assertNotNull($found);
        $this->assertEquals($tag, $found);
    }

    #[Test]
    public function findByName(): void
    {
        $repository = $this->getInstance();

        $tag = $this->createSongTag($this->generateUuid(), 'ロック', 10);

        $repository->save($tag);

        $found = $repository->findByName($tag->name);

        $this->assertNotNull($found);
        $this->assertEquals($tag, $found);
    }

    #[Test]
    public function findByNameNotFound(): void
    {
        $found = $this->getInstance()->findByName(SongTagName::reconstruct('存在しないタグ'));

        $this->assertNull($found);
    }

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $tag = $this->createSongTag($this->generateUuid(), 'ロック', 10);

        $repository->save($tag);

        $found = $repository->find($tag->songTagId);

        $this->assertNotNull($found);
        $this->assertEquals($tag, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(SongTagId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $repository->save($this->createSongTag($this->generateUuid(), 'ロック', 10));
        $repository->save($this->createSongTag($this->generateUuid(), 'ポップ', 30));
        $repository->save($this->createSongTag($this->generateUuid(), 'バラード', 20));

        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    #[Test]
    public function all(): void
    {
        $repository = $this->getInstance();

        $tag1 = $this->createSongTag($this->generateUuid(), 'バラード', 30);
        $tag2 = $this->createSongTag($this->generateUuid(), 'ロック', 10);
        $tag3 = $this->createSongTag($this->generateUuid(), 'ポップ', 20);

        $repository->save($tag1);
        $repository->save($tag2);
        $repository->save($tag3);

        $result = $repository->all();

        $this->assertCount(3, $result);
        $this->assertEquals($tag2, $result[0]);
        $this->assertEquals($tag3, $result[1]);
        $this->assertEquals($tag1, $result[2]);
    }

    #[Test]
    public function allWhenEmpty(): void
    {
        $this->assertSame([], $this->getInstance()->all());
    }

    #[Test]
    public function deleteTag(): void
    {
        $repository = $this->getInstance();

        $tag = $this->createSongTag($this->generateUuid(), 'ロック', 10);

        $repository->save($tag);
        $repository->delete($tag->songTagId);

        $this->assertNull($repository->find($tag->songTagId));
    }

    private function createSongTag(string $songTagId, string $name, int $orderNo): SongTag
    {
        return new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    private function getInstance(): SongTagRepository
    {
        return $this->app->make(SongTagRepository::class);
    }
}
