<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\UseCase;

use App\Models\Song\Song;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateUseCase;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function create(): void
    {
        $person1 = $this->createPerson($this->generateUuid(), '作詞者', 1);
        $person2 = $this->createPerson($this->generateUuid(), '作曲者', 1);
        $person3 = $this->createPerson($this->generateUuid(), '編曲者', 1);

        $this->storePersons($person1, $person2, $person3);

        $result = $this->getInstance()->handle(
            new CreateInputData(
                '描き続けた君へ',
                'オリジナル楽曲',
                'https://example.com/lyrics',
                SongType::Original->value,
                true,
                [],
                [
                    ['personId' => $person1->personId->value, 'role' => 'lyricist', 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 'composer', 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 'arranger', 'orderNo' => 3],
                ],
            ),
        );

        $this->assertTrue($result->isOk());

        $songs = Song::query()->get()->all();
        $this->assertCount(1, $songs);
        $song = array_first($songs);
        $this->assertSame('描き続けた君へ', $song->title);
        $this->assertSame('オリジナル楽曲', $song->description);
        $this->assertSame('https://example.com/lyrics', $song->lyrics_link);
        $this->assertSame(SongType::Original->value, $song->type);
        $this->assertTrue($song->is_display);
        $this->assertSame(10, $song->order_no);
        $this->assertCount(3, $song->persons);
        $this->assertSame($person1->personId->value, $this->toUuid($song->persons[0]->person_id));
        $this->assertSame('lyricist', $song->persons[0]->role);
        $this->assertSame(1, $song->persons[0]->order_no);
        $this->assertSame($person2->personId->value, $this->toUuid($song->persons[1]->person_id));
        $this->assertSame('composer', $song->persons[1]->role);
        $this->assertSame(2, $song->persons[1]->order_no);
        $this->assertSame($person3->personId->value, $this->toUuid($song->persons[2]->person_id));
        $this->assertSame('arranger', $song->persons[2]->role);
        $this->assertSame(3, $song->persons[2]->order_no);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
