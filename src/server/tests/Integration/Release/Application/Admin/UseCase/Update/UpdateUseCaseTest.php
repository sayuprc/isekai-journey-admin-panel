<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Update;

use App\Models\Release\Release as ModelsRelease;
use App\Models\Release\TrackEntry as ModelsTrackEntry;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Update\UpdateInputData;
use Release\Application\Admin\UseCase\Update\UpdateUseCase;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canUpdate(): void
    {
        $songId1 = $this->generateUuid();
        $songId2 = $this->generateUuid();
        $songId3 = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $converter = $this->app->make(UuidConverterInterface::class);

        $this->storeSongs(
            $this->createSong($songId1, '一曲目', '説明', SongType::Original, true, 10),
            $this->createSong($songId2, '二曲目', '説明', SongType::Original, true, 20),
            $this->createSong($songId3, '三曲目', '説明', SongType::Cover, true, 30),
        );
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '旧タイトル',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
                trackEntries: [
                    ['songId' => $songId1, 'trackNo' => 1],
                    ['songId' => $songId2, 'trackNo' => 2],
                ],
            ),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $releaseId,
            title: '新タイトル',
            typeValue: ReleaseType::Single->value,
            distributionTypeValue: ReleaseDistributionType::Physical->value,
            releasedOn: '2026-05-09',
            description: '更新後の説明',
            isDisplay: false,
            trackEntries: [
                ['songId' => $songId3, 'trackNo' => 1],
                ['songId' => $songId1, 'trackNo' => 2],
            ],
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('新タイトル', $result->unwrap()->release->title->value);
        $this->assertSame(2, $result->unwrap()->release->trackEntries->count());

        $this->assertDatabaseHas(ModelsRelease::class, [
            'release_id' => $converter->toBin($releaseId),
            'title' => '新タイトル',
            'type' => ReleaseType::Single->value,
            'distribution_type' => ReleaseDistributionType::Physical->value,
            'description' => '更新後の説明',
            'is_display' => false,
        ]);

        $entries = ModelsTrackEntry::query()
            ->where('release_id', $converter->toBin($releaseId))
            ->orderBy('track_no')
            ->get()
            ->all();

        $this->assertCount(2, $entries);
        $this->assertSame($songId3, $this->toUuid($entries[0]->song_id));
        $this->assertSame(1, $entries[0]->track_no);
        $this->assertSame($songId1, $this->toUuid($entries[1]->song_id));
        $this->assertSame(2, $entries[1]->track_no);
    }

    #[Test]
    public function invalidId(): void
    {
        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: 'invalid-id',
            title: '新タイトル',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: '2026-05-09',
            description: '説明',
            isDisplay: true,
            trackEntries: [],
        ));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
    }

    #[Test]
    public function notFound(): void
    {
        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $this->generateUuid(),
            title: '新タイトル',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: '2026-05-09',
            description: '説明',
            isDisplay: true,
            trackEntries: [],
        ));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(NotFoundError::class, $result->unwrapErr());
    }

    #[Test]
    public function updateFailsWhenTrackEntriesAreDuplicated(): void
    {
        $songId = $this->generateUuid();
        $releaseId = $this->generateUuid();

        $this->storeSongs($this->createSong($songId, '一曲目', '説明', SongType::Original, true, 10));
        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '旧タイトル',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
            ),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $releaseId,
            title: '新タイトル',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: '2026-05-09',
            description: '説明',
            isDisplay: true,
            trackEntries: [
                ['songId' => $songId, 'trackNo' => 1],
                ['songId' => $songId, 'trackNo' => 2],
            ],
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(InvalidInputError::class, $error);
        $this->assertSame(['trackEntries' => ['同じ楽曲を複数指定することはできません。']], $error->errors);
    }

    #[Test]
    public function updateFailsWhenTrackEntriesContainUnknownSong(): void
    {
        $releaseId = $this->generateUuid();

        $this->storeReleases(
            $this->createRelease(
                $releaseId,
                '旧タイトル',
                ReleaseType::Album,
                ReleaseDistributionType::Digital,
                true,
            ),
        );

        $result = $this->getInstance()->handle(new UpdateInputData(
            releaseId: $releaseId,
            title: '新タイトル',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: '2026-05-09',
            description: '説明',
            isDisplay: true,
            trackEntries: [
                ['songId' => $this->generateUuid(), 'trackNo' => 1],
            ],
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('指定された楽曲の一部が存在しません。', $error->message);
    }

    private function getInstance(): UpdateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(UpdateUseCase::class);
    }
}
