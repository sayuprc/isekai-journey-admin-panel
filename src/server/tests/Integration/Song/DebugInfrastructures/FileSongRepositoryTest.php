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
