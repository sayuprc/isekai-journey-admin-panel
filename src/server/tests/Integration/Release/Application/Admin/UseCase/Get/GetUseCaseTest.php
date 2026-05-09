<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Get;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Get\GetInputData;
use Release\Application\Admin\UseCase\Get\GetUseCase;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
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
        $releaseId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId1, '一曲目', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, '二曲目', '説明', SongType::Original, true, 20),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '観測された春',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                trackEntries: [
                    ['songId' => $songId2, 'trackNo' => 2],
                    ['songId' => $songId1, 'trackNo' => 1],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new GetInputData($releaseId));

        $this->assertTrue($result->isOk());
        $this->assertSame($releaseId, $result->unwrap()->release->releaseId->value);
        $this->assertCount(2, $result->unwrap()->songs);
        $this->assertSame('一曲目', $result->unwrap()->songs[0]->title);
        $this->assertSame(1, $result->unwrap()->songs[0]->trackNo);
        $this->assertSame('二曲目', $result->unwrap()->songs[1]->title);
        $this->assertSame(2, $result->unwrap()->songs[1]->trackNo);
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
