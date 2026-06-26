<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Create;

use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Create\CreateInputData;
use Release\Application\Admin\UseCase\Create\CreateUseCase;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseType;
use Song\Domain\Models\SongType;
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

        $this->storeSongs(
            $this->createSong($songId, 'テスト楽曲1', '説明', SongType::Original, true, 1),
        );

        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: '2026-05-09',
            description: '',
            isDisplay: true,
            trackEntries: [
                ['songId' => $songId, 'trackNo' => 1],
            ],
        ));

        $this->assertTrue($result->isOk());
        $this->assertSame('観測された春', $result->unwrap()->release->title->value);
        $this->assertCount(1, $result->unwrap()->release->trackEntries->toGeneric());

        $this->assertDatabaseHas('releases', [
            'title' => '観測された春',
            'type' => ReleaseType::Album->value,
            'distribution_type' => ReleaseDistributionType::Digital->value,
            'description' => '',
            'is_display' => true,
        ]);
        $this->assertDatabaseCount('release_track_entries', 1);
    }

    #[Test]
    public function createFailsWhenReleasedOnIsInvalid(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData(
            title: '観測された春',
            typeValue: ReleaseType::Album->value,
            distributionTypeValue: ReleaseDistributionType::Digital->value,
            releasedOn: 'invalid-date',
            description: '説明',
            isDisplay: true,
            trackEntries: [],
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
