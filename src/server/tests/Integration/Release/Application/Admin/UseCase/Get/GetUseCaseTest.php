<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Get;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Get\GetInputData;
use Release\Application\Admin\UseCase\Get\GetUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function found(): void
    {
        $songId1 = $this->generateUuid();
        $songId2 = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId1, 'テスト楽曲1', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, 'テスト楽曲2', '説明', SongType::Original, true, 20),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                $releaseGroupId,
                '初回限定盤',
                true,
                formats: [ReleaseFormat::Cd->value],
                media: [
                    [
                        'position' => 2,
                        'name' => null,
                        'tracks' => [['songId' => $songId2, 'title' => null, 'trackNo' => 1]],
                    ],
                    [
                        'position' => 1,
                        'name' => null,
                        'tracks' => [['songId' => $songId1, 'title' => null, 'trackNo' => 1]],
                    ],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new GetInputData($releaseId));

        $this->assertTrue($result->isOk());
        $this->assertSame($releaseId, $result->unwrap()->release->releaseId->value);
        $this->assertCount(2, $result->unwrap()->release->media->toGeneric());

        // 収録曲は媒体順 → 曲順で並ぶ。
        $this->assertCount(2, $result->unwrap()->songs);
        $this->assertSame(1, $result->unwrap()->songs[0]->mediumPosition);
        $this->assertSame('テスト楽曲1', $result->unwrap()->songs[0]->title);
        $this->assertSame(2, $result->unwrap()->songs[1]->mediumPosition);
        $this->assertSame('テスト楽曲2', $result->unwrap()->songs[1]->title);
    }

    #[Test]
    public function invalidId(): void
    {
        $result = $this->getInstance()->handle(new GetInputData('invalid-id'));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(InvalidInputError::class, $error);
    }

    #[Test]
    public function notFound(): void
    {
        $result = $this->getInstance()->handle(new GetInputData($this->generateUuid()));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(NotFoundError::class, $result->unwrapErr());
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
