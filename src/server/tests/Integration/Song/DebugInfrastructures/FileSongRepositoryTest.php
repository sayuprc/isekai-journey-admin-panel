<?php

declare(strict_types=1);

namespace Tests\Integration\Song\DebugInfrastructures;

use PHPUnit\Framework\Attributes\Test;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Song;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class FileSongRepositoryTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function all(): void
    {
        $this->store(
            $song1 = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                1,
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
            $song2 = $this->createSong(
                $this->generateUuid(),
                '全部夢だった！',
                'カバー楽曲',
                SongType::Cover,
                2,
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
                [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            ),
        );

        $songs = $this->getInstance()->all();

        $this->assertCount(2, $songs);
        $this->assertEquals([$song1, $song2], $songs);
    }

    #[Test]
    public function find(): void
    {
        $this->store(
            $song = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                1,
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
            1,
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $this->generateUuid(), 'orderNo' => 1]],
        );

        $this->getInstance()->save($song);

        $found = $this->getAll(FileSongRepository::class);

        $this->assertCount(1, $found);
        $this->assertEquals($song, array_first($found));
    }

    private function store(Song ...$songs): void
    {
        array_map(
            fn (Song $song) => $this->factory(FileSongRepository::class, $song->songId->value, $song),
            $songs,
        );
    }

    private function getInstance(): FileSongRepository
    {
        return $this->app->make(FileSongRepository::class);
    }
}
