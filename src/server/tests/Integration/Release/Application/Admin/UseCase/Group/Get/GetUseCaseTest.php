<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Get;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Get\GetInputData;
use Release\Application\Admin\UseCase\Group\Get\GetUseCase;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
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
        $releaseGroupId = $this->generateUuid();
        $releaseId1 = $this->generateUuid();
        $releaseId2 = $this->generateUuid();

        $this->storeReleaseGroups(
            $this->createReleaseGroup($releaseGroupId, '観測された春', ReleaseGroupType::Album, true),
        );
        $this->storeReleases(
            $this->createRelease($releaseId2, $releaseGroupId, 'CD+DVD', true, new ImmutableDate('2026-06-01'), formats: [ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value], media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
                ['position' => 2, 'name' => null, 'tracks' => []],
            ], orderNo: 10),
            $this->createRelease($releaseId1, $releaseGroupId, '配信', true, new ImmutableDate('2026-05-01'), formats: [ReleaseFormat::Digital->value], media: [
                ['position' => 1, 'name' => null, 'tracks' => []],
            ], orderNo: 20),
        );

        $result = $this->getInstance()->handle(new GetInputData($releaseGroupId));

        $this->assertTrue($result->isOk());
        $this->assertSame($releaseGroupId, $result->unwrap()->releaseGroup->releaseGroupId->value);

        // 傘下リリースは表示順、formatValues は提供形態の値順。
        $releases = $result->unwrap()->releases;
        $this->assertCount(2, $releases);
        $this->assertSame($releaseId2, $releases[0]->releaseId);
        $this->assertSame(10, $releases[0]->orderNo);
        $this->assertSame([ReleaseFormat::Cd->value, ReleaseFormat::Dvd->value], $releases[0]->formatValues);
        $this->assertSame($releaseId1, $releases[1]->releaseId);
        $this->assertSame(20, $releases[1]->orderNo);
        $this->assertSame([ReleaseFormat::Digital->value], $releases[1]->formatValues);
    }

    #[Test]
    public function invalidId(): void
    {
        $result = $this->getInstance()->handle(new GetInputData('invalid-id'));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(InvalidInputError::class, $result->unwrapErr());
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
