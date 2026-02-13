<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\GetInteractor;
use Song\Application\UseCase\Get\GetInputData;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function getSong(): void
    {
        $arranger = $this->createCreator($arrangerId = $this->generateUuid(), '編曲者A');
        $composer = $this->createCreator($composerId = $this->generateUuid(), '作曲者A');
        $lyricist = $this->createCreator($lyricistId = $this->generateUuid(), '作詞者A');

        $this->storeCreators($arranger, $composer, $lyricist);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                1,
                [['creatorId' => $arrangerId, 'orderNo' => 1]],
                [['creatorId' => $composerId, 'orderNo' => 1]],
                [['creatorId' => $lyricistId, 'orderNo' => 1]],
            ),
        );

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($songId, $response->song->songId);
        $this->assertSame('描き続けた君へ', $response->song->title);
        $this->assertSame('オリジナル楽曲', $response->song->description);
        $this->assertSame(SongType::Original->getName(), $response->song->songTypeName);
        $this->assertSame(SongType::Original->value, $response->song->songTypeValue);
        $this->assertSame(1, $response->song->orderNo);
        $this->assertCount(1, $response->song->arrangers);
        $this->assertSame($arrangerId, $response->song->arrangers[0]->creatorId);
        $this->assertSame('編曲者A', $response->song->arrangers[0]->creatorName);
        $this->assertCount(1, $response->song->composers);
        $this->assertSame($composerId, $response->song->composers[0]->creatorId);
        $this->assertSame('作曲者A', $response->song->composers[0]->creatorName);
        $this->assertCount(1, $response->song->lyricists);
        $this->assertSame($lyricistId, $response->song->lyricists[0]->creatorId);
        $this->assertSame('作詞者A', $response->song->lyricists[0]->creatorName);
    }

    #[Test]
    public function failureGetSong(): void
    {
        $songId = $this->generateUuid();

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $this->assertFalse($result->isOk());

        $this->assertSame('楽曲が見つかりません: ' . $songId, $result->unwrapErr());
    }

    private function getInstance(): GetInteractor
    {
        return $this->app->make(GetInteractor::class);
    }
}
