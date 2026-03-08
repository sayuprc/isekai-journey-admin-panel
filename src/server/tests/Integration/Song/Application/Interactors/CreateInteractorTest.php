<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Interactors\CreateInteractor;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Domain\Models\SongRepositoryInterface;
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
        $creator1 = $this->createCreator($this->generateUuid(), '', 1);
        $creator2 = $this->createCreator($this->generateUuid(), '', 1);
        $creator3 = $this->createCreator($this->generateUuid(), '', 1);

        $this->storeCreators($creator1, $creator2, $creator3);

        $result = $this->getInstance()->handle(
            new CreateInputData(
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original->value,
                [['creatorId' => $creator1->creatorId->value]],
                [['creatorId' => $creator2->creatorId->value]],
                [['creatorId' => $creator3->creatorId->value]],
            ),
        );

        $this->assertTrue($result->isOk());

        $songs = $this->app->make(SongRepositoryInterface::class)->all();
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('描き続けた君へ', $song->title->value);
        $this->assertSame('オリジナル楽曲', $song->description->value);
        $this->assertSame(SongType::Original, $song->type);
        $this->assertSame(10, $song->orderNo->value);
        $this->assertCount(1, $song->lyricists);
        $this->assertSame($creator1->creatorId->value, $song->lyricists[0]->creatorId->value);
        $this->assertSame(1, $song->lyricists[0]->orderNo->value);
        $this->assertCount(1, $song->composers);
        $this->assertSame($creator2->creatorId->value, $song->composers[0]->creatorId->value);
        $this->assertSame(1, $song->composers[0]->orderNo->value);
        $this->assertCount(1, $song->arrangers);
        $this->assertSame($creator3->creatorId->value, $song->arrangers[0]->creatorId->value);
        $this->assertSame(1, $song->arrangers[0]->orderNo->value);
    }

    private function getInstance(): CreateInteractor
    {
        $this->privilegedContext();

        return $this->app->make(CreateInteractor::class);
    }
}
