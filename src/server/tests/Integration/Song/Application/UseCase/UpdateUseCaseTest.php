<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\UseCase;

use App\Models\Song\Song;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Application\UseCase\Update\UpdateUseCase;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $person1 = $this->createPerson($this->generateUuid(), '作詞者', 1);
        $person2 = $this->createPerson($this->generateUuid(), '作曲者', 1);
        $person3 = $this->createPerson($this->generateUuid(), '編曲者', 1);

        $this->storePersons($person1, $person2, $person3);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '曲名',
                '説明',
                SongType::Original,
                true,
                1,
                [],
                [
                    ['personId' => $person1->personId->value, 'role' => 1, 'orderNo' => 1],
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 2],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 3],
                ],
            ),
        );

        $result = $this->getInstance()->handle(
            new UpdateInputData(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                'https://example.com/lyrics',
                SongType::Cover->value,
                false,
                2,
                [],
                [
                    ['personId' => $person2->personId->value, 'role' => 2, 'orderNo' => 1],
                    ['personId' => $person3->personId->value, 'role' => 3, 'orderNo' => 2],
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
        $this->assertSame(SongType::Cover->value, $song->type);
        $this->assertFalse($song->is_display);
        $this->assertSame(2, $song->order_no);
        $this->assertCount(2, $song->persons);
        $this->assertSame($person2->personId->value, $this->toUuid($song->persons[0]->person_id));
        $this->assertSame(2, $song->persons[0]->role);
        $this->assertSame($person3->personId->value, $this->toUuid($song->persons[1]->person_id));
        $this->assertSame(3, $song->persons[1]->role);

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Update, AuditTargetType::Song, $songId);
        $this->assertSame('描き続けた君へ', $log['snapshot']['title']);
        $this->assertCount(2, $log['snapshot']['persons']);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
