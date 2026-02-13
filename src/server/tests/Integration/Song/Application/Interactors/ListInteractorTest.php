<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\ListInteractor;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function nonEmptySongs(): void
    {
        $arranger = $this->createCreator($arrangerId = $this->generateUuid(), '編曲者A');
        $composer = $this->createCreator($composerId = $this->generateUuid(), '作曲者A');
        $lyricist = $this->createCreator($lyricistId = $this->generateUuid(), '作詞者A');

        $this->storeCreators($arranger, $composer, $lyricist);

        $this->storeSongs(
            $song1 = $this->createSong(
                $this->generateUuid(),
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                1,
                [['creatorId' => $arrangerId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [['creatorId' => $lyricistId, 'orderNo' => 1]],
            ),
            $song2 = $this->createSong(
                $this->generateUuid(),
                '全部夢だった！',
                'カバー楽曲',
                SongType::Cover,
                2,
                [['creatorId' => $arrangerId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [],
            ),
        );

        $response = $this->getInstance()->handle();

        $this->assertCount(2, $response->songs);

        $this->assertSame($song1->songId->value, $response->songs[0]->songId);
        $this->assertSame('描き続けた君へ', $response->songs[0]->title);
        $this->assertSame(SongType::Original->getName(), $response->songs[0]->songTypeName);
        $this->assertSame(SongType::Original->value, $response->songs[0]->songTypeValue);
        $this->assertSame(1, $response->songs[0]->orderNo);
        $this->assertCount(1, $response->songs[0]->arrangers);
        $this->assertSame($arrangerId, $response->songs[0]->arrangers[0]->creatorId);
        $this->assertSame('編曲者A', $response->songs[0]->arrangers[0]->creatorName);
        $this->assertCount(1, $response->songs[0]->composers);
        $this->assertSame($composerId, $response->songs[0]->composers[0]->creatorId);
        $this->assertSame('作曲者A', $response->songs[0]->composers[0]->creatorName);
        $this->assertCount(1, $response->songs[0]->lyricists);
        $this->assertSame($lyricistId, $response->songs[0]->lyricists[0]->creatorId);
        $this->assertSame('作詞者A', $response->songs[0]->lyricists[0]->creatorName);

        $this->assertSame($song2->songId->value, $response->songs[1]->songId);
        $this->assertSame('全部夢だった！', $response->songs[1]->title);
        $this->assertSame(SongType::Cover->getName(), $response->songs[1]->songTypeName);
        $this->assertSame(SongType::Cover->value, $response->songs[1]->songTypeValue);
        $this->assertSame(2, $response->songs[1]->orderNo);
        $this->assertCount(1, $response->songs[1]->arrangers);
        $this->assertSame($arrangerId, $response->songs[1]->arrangers[0]->creatorId);
        $this->assertCount(1, $response->songs[1]->composers);
        $this->assertSame($composerId, $response->songs[1]->composers[0]->creatorId);
        $this->assertCount(0, $response->songs[1]->lyricists);
    }

    private function getInstance(): ListInteractor
    {
        return $this->app->make(ListInteractor::class);
    }
}
