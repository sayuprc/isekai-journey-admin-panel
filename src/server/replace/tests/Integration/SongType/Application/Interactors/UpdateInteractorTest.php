<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\UpdateInteractor;
use SongType\Application\UseCase\Update\UpdateInputData;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class UpdateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canUpdate(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('楽曲種別'), new OrderNo(2))
        );

        $result = $this->getInstance()->handle(new UpdateInputData($uuid, 'オリジナル', 1));

        $this->assertTrue($result->isOk());

        /** @var array<SongType> $songTypes */
        $songTypes = $this->getAll(FileSongTypeRepository::class);
        $this->assertCount(1, $songTypes);
        $this->assertSame($uuid, $songTypes[$uuid]->songTypeId->value);
        $this->assertSame('オリジナル', $songTypes[$uuid]->songTypeName->value);
        $this->assertSame(1, $songTypes[$uuid]->orderNo->value);
    }

    #[Test]
    public function updateFailsIfNameAlreadyExists(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid1,
            new SongType(new SongTypeId($uuid1), new SongTypeName('オリジナル'), new OrderNo(1))
        );
        $this->factory(
            FileSongTypeRepository::class,
            $uuid2,
            new SongType(new SongTypeId($uuid2), new SongTypeName('カバー'), new OrderNo(2))
        );

        $result = $this->getInstance()->handle(new UpdateInputData($uuid2, 'オリジナル', 1));

        $this->assertTrue($result->isErr());

        /** @var array<SongType> $songTypes */
        $songTypes = $this->getAll(FileSongTypeRepository::class);
        $this->assertCount(2, $songTypes);
        $this->assertSame($uuid1, $songTypes[$uuid1]->songTypeId->value);
        $this->assertSame('オリジナル', $songTypes[$uuid1]->songTypeName->value);
        $this->assertSame(1, $songTypes[$uuid1]->orderNo->value);
        $this->assertSame($uuid2, $songTypes[$uuid2]->songTypeId->value);
        $this->assertSame('カバー', $songTypes[$uuid2]->songTypeName->value);
        $this->assertSame(2, $songTypes[$uuid2]->orderNo->value);
    }

    private function getInstance(): UpdateInteractor
    {
        return $this->container->get(UpdateInteractor::class);
    }
}
