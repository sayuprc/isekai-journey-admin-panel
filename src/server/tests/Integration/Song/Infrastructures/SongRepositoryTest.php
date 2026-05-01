<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Creator\Infrastructures\CreatorRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class SongRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function find(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, true, 1, [], [], [], []);

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function findNotFound(): void
    {
        $found = $this->getInstance()->find(SongId::reconstruct($this->generateUuid()));

        $this->assertNull($found);
    }

    #[Test]
    public function findWithCreators(): void
    {
        $creatorRepository = $this->app->make(CreatorRepository::class);
        $creator = $this->createCreator($this->generateUuid(), 'クリエイター', 1);
        $creatorRepository->save($creator);

        $repository = $this->getInstance();

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [],
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
        );

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function isCreatorUsed(): void
    {
        $creatorRepository = $this->app->make(CreatorRepository::class);
        $creator = $this->createCreator($this->generateUuid(), 'クリエイター', 1);
        $creatorRepository->save($creator);

        $repository = $this->getInstance();

        $song = $this->createSong(
            $this->generateUuid(),
            '曲名',
            '説明',
            SongType::Original,
            true,
            1,
            [],
            [['creatorId' => $creator->creatorId->value, 'orderNo' => 1]],
            [],
            [],
        );

        $repository->save($song);

        $this->assertTrue($repository->isCreatorUsed($creator->creatorId));
    }

    #[Test]
    public function isCreatorNotUsed(): void
    {
        $this->assertFalse($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($this->generateUuid())));
    }

    #[Test]
    public function save(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, true, 1, [], [], [], []);

        $repository->save($song);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function saveUpdatesExisting(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '旧タイトル', '旧説明', SongType::Original, true, 1, [], [], [], []);
        $repository->save($song);

        $updated = $this->createSong($song->songId->value, '新タイトル', '新説明', SongType::Cover, true, 2, [], [], [], []);
        $repository->save($updated);

        $found = $repository->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($updated, $found);
    }

    #[Test]
    public function deleting(): void
    {
        $repository = $this->getInstance();

        $song = $this->createSong($this->generateUuid(), '曲名', '説明', SongType::Original, true, 1, [], [], [], []);

        $repository->save($song);
        $repository->delete($song->songId);

        $found = $repository->find($song->songId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        $song1 = $this->createSong($this->generateUuid(), '曲1', '説明', SongType::Original, true, 10, [], [], [], []);
        $song2 = $this->createSong($this->generateUuid(), '曲2', '説明', SongType::Original, true, 30, [], [], [], []);
        $song3 = $this->createSong($this->generateUuid(), '曲3', '説明', SongType::Original, true, 20, [], [], [], []);

        $repository->save($song1);
        $repository->save($song2);
        $repository->save($song3);

        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function getMaxOrderNoWhenEmpty(): void
    {
        $this->assertSame(0, $this->getInstance()->getMaxOrderNo());
    }

    private function getInstance(): SongRepository
    {
        return $this->app->make(SongRepository::class);
    }
}
