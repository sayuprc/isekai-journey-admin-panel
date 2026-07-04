<?php

declare(strict_types=1);

namespace Tests\Integration\Release\Application\Admin\UseCase\Group\Get;

use DateType\ImmutableDate;
use PHPUnit\Framework\Attributes\Test;
use Release\Application\Admin\UseCase\Group\Get\GetInputData;
use Release\Application\Admin\UseCase\Group\Get\GetUseCase;
use Release\Domain\Models\MediumFormat;
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
            $this->createRelease($releaseId2, $releaseGroupId, 'CD+DVD', true, new ImmutableDate('2026-06-01'), media: [
                ['position' => 1, 'format' => MediumFormat::Cd->value, 'tracks' => []],
                ['position' => 2, 'format' => MediumFormat::Dvd->value, 'tracks' => []],
            ]),
            $this->createRelease($releaseId1, $releaseGroupId, '配信', true, new ImmutableDate('2026-05-01'), media: [
                ['position' => 1, 'format' => MediumFormat::Digital->value, 'tracks' => []],
            ]),
        );

        $result = $this->getInstance()->handle(new GetInputData($releaseGroupId));

        $this->assertTrue($result->isOk());
        $this->assertSame($releaseGroupId, $result->unwrap()->releaseGroup->releaseGroupId->value);

        // 傘下リリースは発売日昇順、formatValues は媒体順。
        $releases = $result->unwrap()->releases;
        $this->assertCount(2, $releases);
        $this->assertSame($releaseId1, $releases[0]->releaseId);
        $this->assertSame('2026-05-01', $releases[0]->releasedOn);
        $this->assertSame([MediumFormat::Digital->value], $releases[0]->formatValues);
        $this->assertSame($releaseId2, $releases[1]->releaseId);
        $this->assertSame([MediumFormat::Cd->value, MediumFormat::Dvd->value], $releases[1]->formatValues);
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
