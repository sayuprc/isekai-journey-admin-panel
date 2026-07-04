<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Create;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Release\Domain\Models\MediumFormat;
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
            isDisplay: true,
            media: [
                [
                    'position' => 1,
                    'formatValue' => MediumFormat::Cd->value,
                    'tracks' => [['songId' => $songId, 'trackNo' => 1]],
                ],
                [
                    'position' => 2,
                    'formatValue' => MediumFormat::Dvd->value,
                    'tracks' => [],
                ],
            ],
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('初回限定盤', $result->unwrap()->release->name->value);
        $this->assertSame($releaseGroupId, $result->unwrap()->release->releaseGroupId->value);
        $this->assertCount(2, $result->unwrap()->release->media->toGeneric());

        $this->assertDatabaseHas('releases', [
            'name' => '初回限定盤',
            'description' => '',
            'is_display' => true,
        ]);
        $this->assertDatabaseCount('release_media', 2);
        $this->assertDatabaseCount('release_tracks', 1);
    }

    #[Test]
    public function createFailsWhenReleaseGroupDoesNotExist(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            releaseGroupId: $this->generateUuid(),
            name: '通常盤',
            releasedOn: '2026-05-09',
            description: '',
            isDisplay: true,
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
            isDisplay: true,
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
