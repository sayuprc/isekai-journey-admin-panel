<?php

declare(strict_types=1);

namespace Tests\Integration\Song\DebugInfrastructures;

use Creator\Domain\Models\CreatorId;
use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileSongRepositoryTest extends TestCase
{
    use EntityFactory;
    use EntityStore;
    use FileRepositoryTransaction;

    #[Test]
    public function find(): void
    {
        $this->storeSongs(
            $song = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                true,
                1,
                [],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
        );

        $found = $this->getInstance()->find($song->songId);

        $this->assertNotNull($found);
        $this->assertEquals($song, $found);
    }

    #[Test]
    public function save(): void
    {
        $song = $this->createSong(
            $this->generateUuid(),
            '描き続けた君へ',
            'オリジナル楽曲',
            SongType::Original,
            true,
            1,
            [],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
        );

        $this->getInstance()->save($song);

        $found = $this->getAll(Song::class, FileSongRepository::class);

        $this->assertCount(1, $found);
        $this->assertEquals($song, array_first($found));
    }

    #[Test]
    public function deleting(): void
    {
        $this->storeSongs(
            $song = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                true,
                1,
                [],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
        );

        $this->getInstance()->delete($song->songId);

        $found = $this->getInstance()->find($song->songId);

        $this->assertNull($found);
    }

    #[Test]
    public function getMaxOrderNo(): void
    {
        $repository = $this->getInstance();

        // 空の場合は 0
        $this->assertSame(0, $repository->getMaxOrderNo());

        // データを追加
        $this->storeSongs(
            $this->createSong($this->generateUuid(), '曲1', '説明', SongType::Original, true, 10, [], [], [], []),
            $this->createSong($this->generateUuid(), '曲2', '説明', SongType::Original, true, 30, [], [], [], []),
            $this->createSong($this->generateUuid(), '曲3', '説明', SongType::Original, true, 20, [], [], [], []),
        );

        // 最大値が返ることを確認
        $this->assertSame(30, $repository->getMaxOrderNo());
    }

    #[Test]
    public function isCreatorUsedInArranger(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '曲1',
                '説明',
                SongType::Original,
                true,
                1,
                [],
                [],
                [],
                [['creatorId' => $creatorId, 'orderNo' => 1]],
            ),
        );

        $this->assertTrue($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function isCreatorUsedInComposer(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '曲2',
                '説明',
                SongType::Original,
                true,
                1,
                [],
                [],
                [['creatorId' => $creatorId, 'orderNo' => 1]],
                [],
            ),
        );

        $this->assertTrue($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function isCreatorUsedInLyricist(): void
    {
        $creatorId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $this->generateUuid(),
                '曲3',
                '説明',
                SongType::Original,
                true,
                1,
                [],
                [['creatorId' => $creatorId, 'orderNo' => 1]],
                [],
                [],
            ),
        );

        $this->assertTrue($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    #[Test]
    public function isCreatorUsedNotFound(): void
    {
        $creatorId = $this->generateUuid();

        $this->assertFalse($this->getInstance()->isCreatorUsed(CreatorId::reconstruct($creatorId)));
    }

    private function getInstance(): FileSongRepository
    {
        return $this->app->make(FileSongRepository::class);
    }
}
