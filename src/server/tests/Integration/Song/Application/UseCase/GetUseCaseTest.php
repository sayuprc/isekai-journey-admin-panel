<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\UseCase;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\UseCase\Get\GetInputData;
use Song\Application\UseCase\Get\GetUseCase;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function getSong(): void
    {
        $lyricist = $this->createPerson($lyricistId = $this->generateUuid(), '作詞者A', 1);
        $composer = $this->createPerson($composerId = $this->generateUuid(), '作曲者A', 1);
        $arranger = $this->createPerson($arrangerId = $this->generateUuid(), '編曲者A', 1);

        $this->storePersons($lyricist, $composer, $arranger);

        $songId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong(
                $songId,
                '描き続けた君へ',
                'オリジナル楽曲',
                SongType::Original,
                true,
                1,
                [],
                [
                    ['personId' => $lyricistId, 'role' => 'lyricist', 'orderNo' => 1],
                    ['personId' => $composerId, 'role' => 'composer', 'orderNo' => 2],
                    ['personId' => $arrangerId, 'role' => 'arranger', 'orderNo' => 3],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame($songId, $response->song->songId);
        $this->assertSame('描き続けた君へ', $response->song->title);
        $this->assertSame('オリジナル楽曲', $response->song->description);
        $this->assertSame(SongType::Original->getName(), $response->song->typeName);
        $this->assertSame(SongType::Original->value, $response->song->typeValue);
        $this->assertSame(1, $response->song->orderNo);
        $this->assertCount(3, $response->song->persons);
        $this->assertSame($lyricistId, $response->song->persons[0]->personId);
        $this->assertSame('作詞者A', $response->song->persons[0]->name);
        $this->assertSame('lyricist', $response->song->persons[0]->role);
        $this->assertSame($composerId, $response->song->persons[1]->personId);
        $this->assertSame('composer', $response->song->persons[1]->role);
        $this->assertSame($arrangerId, $response->song->persons[2]->personId);
        $this->assertSame('arranger', $response->song->persons[2]->role);
    }

    #[Test]
    public function failureGetSong(): void
    {
        $songId = $this->generateUuid();

        $result = $this->getInstance()->handle(new GetInputData($songId));

        $this->assertFalse($result->isOk());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('楽曲', $error->resourceName);
        $this->assertSame($songId, $error->identifier);
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
