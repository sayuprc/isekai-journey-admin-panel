<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Create;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class CreateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canCreate(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            jacketArtUrl: 'https://example.com/jacket.png',
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => $songId, 'title' => null, 'trackNo' => 1]],
                ],
                [
                    'position' => 2,
                    'name' => null,
                    'tracks' => [],
                ],
            ],
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('初回限定盤', $result->unwrap()->release->name->value);
        $this->assertSame($releaseGroupId, $result->unwrap()->release->releaseGroupId->value);
        $this->assertSame(10, $result->unwrap()->release->orderNo->value);
        $this->assertCount(2, $result->unwrap()->release->media->toGeneric());

        $this->assertDatabaseHas('releases', [
            'name' => '初回限定盤',
            'description' => '',
            'jacket_art_url' => 'https://example.com/jacket.png',
            'is_display' => true,
            'order_no' => 10,
        ]);
        $this->assertDatabaseCount('release_media', 2);
        $this->assertDatabaseCount('release_tracks', 1);
    }

    #[Test]
    public function canCreateWithTitleOnlyTrack(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            jacketArtUrl: null,
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [
                        ['songId' => $songId, 'title' => null, 'trackNo' => 1],
                        ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
                    ],
                ],
            ],
        ));

        $this->assertTrue($result->isOk());

        $this->assertDatabaseCount('release_tracks', 2);
        $this->assertDatabaseHas('release_tracks', [
            'track_no' => 2,
            'song_id' => null,
            'title' => '管理対象外の楽曲',
        ]);
    }

    #[Test]
    public function canCreateWithOverriddenTrackTitle(): void
    {
        $songId = $this->generateUuid();
        $releaseGroupId = $this->generateUuid();

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );
        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        // 楽曲への紐づきを維持したまま表示名だけを上書きするケース。
        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            jacketArtUrl: null,
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => $songId, 'title' => 'テスト楽曲1 -instrumental-', 'trackNo' => 1]],
                ],
            ],
        ));

        $this->assertTrue($result->isOk());

        $this->assertDatabaseHas('release_tracks', [
            'track_no' => 1,
            'title' => 'テスト楽曲1 -instrumental-',
        ]);
    }

    #[Test]
    public function createFailsWhenTrackHasNeitherSongIdNorTitle(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '初回限定盤',
            releasedOn: '2026-05-09',
            description: '',
            jacketArtUrl: null,
            isDisplay: true,
            orderNo: 10,
            formatValues: [ReleaseFormat::Cd->value],
            media: [
                [
                    'position' => 1,
                    'name' => null,
                    'tracks' => [['songId' => null, 'title' => null, 'trackNo' => 1]],
                ],
            ],
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(InvalidInputError::class, $error);
        $this->assertSame(['media' => ['収録曲には楽曲かタイトルの少なくとも一方を指定してください。']], $error->errors);
    }

    #[Test]
    public function createFailsWhenReleaseGroupDoesNotExist(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $this->generateUuid(),
            name: '通常盤',
            releasedOn: '2026-05-09',
            description: '',
            jacketArtUrl: null,
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [],
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessLogicError::class, $error);
        $this->assertSame('指定されたリリースグループが存在しません。', $error->message);
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $releaseGroupId = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $releaseGroupId,
            name: '通常盤',
            releasedOn: 'invalid-date',
            description: '説明',
            jacketArtUrl: null,
            isDisplay: true,
            orderNo: 1,
            formatValues: [ReleaseFormat::Cd->value],
            media: [],
        ));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(InvalidInputError::class, $error);
        $this->assertSame(['releasedOn' => ['発売日が不正です']], $error->errors);
    }

    private function getInstance(): CreateUseCase
    {
        $this->privilegedContext();

        return $this->app->make(CreateUseCase::class);
    }
}
