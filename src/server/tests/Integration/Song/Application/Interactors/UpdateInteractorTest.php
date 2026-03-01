<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\UpdateInteractor;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Song;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), '編曲者', 1);
        $creator2 = $this->createCreator($this->generateUuid(), '作曲者', 1);
        $creator3 = $this->createCreator($this->generateUuid(), '作詞者', 1);

        $this->storeCreators($creator1, $creator2, $creator3);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                1,
                [['creatorId' => $creator1->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator3->creatorId->value, 'orderNo' => 1]],
            ),
        );

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Cover->value,
                2,
                [['creatorId' => $creator1->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                [],
            ),
        );

        $this->assertTrue($result->isOk());

        $songs = $this->getAll(Song::class, FileSongRepository::class);
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('描き続けた君へ', $song->title->value);
        $this->assertSame('オリジナル楽曲', $song->description->value);
        $this->assertSame(SongType::Cover, $song->songType);
        $this->assertSame(2, $song->orderNo->value);
        $this->assertCount(1, $song->arrangers);
        $this->assertSame($creator1->creatorId->value, $song->arrangers[0]->creatorId->value);
        $this->assertCount(1, $song->composers);
        $this->assertSame($creator2->creatorId->value, $song->composers[0]->creatorId->value);
        $this->assertCount(0, $song->lyricists);
    }

    private function getInstance(): UpdateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(UpdateInteractor::class);
    }
}
