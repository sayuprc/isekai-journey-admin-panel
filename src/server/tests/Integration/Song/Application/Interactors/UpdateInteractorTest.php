<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use App\Models\Song\Song;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\UpdateInteractor;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateInteractorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), '作詞者', 1);
        $creator2 = $this->createCreator($this->generateUuid(), '作曲者', 1);
        $creator3 = $this->createCreator($this->generateUuid(), '編曲者', 1);

        $this->storeCreators($creator1, $creator2, $creator3);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                null,
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
                [],
                [['creatorId' => $creator2->creatorId->value, 'orderNo' => 1]],
                [['creatorId' => $creator3->creatorId->value, 'orderNo' => 1]],
            ),
        );

        $this->assertTrue($result->isOk());

        $songs = Song::query()->get()->all();
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('描き続けた君へ', $song->title);
        $this->assertSame('オリジナル楽曲', $song->description);
        $this->assertSame(SongType::Cover->value, $song->type);
        $this->assertSame(2, $song->order_no);
        $this->assertCount(0, $song->lyricists);
        $this->assertCount(1, $song->composers);
        $this->assertSame($creator2->creatorId->value, $this->toUuid($song->composers->first()->creator_id));
        $this->assertCount(1, $song->arrangers);
        $this->assertSame($creator3->creatorId->value, $this->toUuid($song->arrangers->first()->creator_id));
    }

    private function getInstance(): UpdateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(UpdateInteractor::class);
    }
}
