<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use App\Models\Song\Song;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateInteractorTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function create(): void
    {
        $creator1 = $this->createCreator($this->generateUuid(), '作詞者', 1);
        $creator2 = $this->createCreator($this->generateUuid(), '作曲者', 1);
        $creator3 = $this->createCreator($this->generateUuid(), '編曲者', 1);

        $this->storeCreators($creator1, $creator2, $creator3);

        $result = $this->getInstance()->handle(
            new CreateInputData(
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original->value,
                true,
                [['creatorId' => $creator1->creatorId->value]],
                [['creatorId' => $creator2->creatorId->value]],
                [['creatorId' => $creator3->creatorId->value]],
            ),
        );

        $this->assertTrue($result->isOk());

        $songs = Song::query()->get()->all();
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('描き続けた君へ', $song->title);
        $this->assertSame('オリジナル楽曲', $song->description);
        $this->assertSame(SongType::Original->value, $song->type);
        $this->assertTrue($song->is_display);
        $this->assertSame(10, $song->order_no);
        $this->assertCount(1, $song->lyricists);
        $this->assertSame($creator1->creatorId->value, $this->toUuid($song->lyricists->first()->creator_id));
        $this->assertSame(1, $song->lyricists->first()->order_no);
        $this->assertCount(1, $song->composers);
        $this->assertSame($creator2->creatorId->value, $this->toUuid($song->composers->first()->creator_id));
        $this->assertSame(1, $song->composers->first()->order_no);
        $this->assertCount(1, $song->arrangers);
        $this->assertSame($creator3->creatorId->value, $this->toUuid($song->arrangers->first()->creator_id));
        $this->assertSame(1, $song->arrangers->first()->order_no);
    }

    private function getInstance(): CreateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(CreateInteractor::class);
    }
}
