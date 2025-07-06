<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\CreateInteractor;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class CreateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function create(): void
    {
        $result = $this->getInstance()->handle(new CreateInputData('オリジナル', 1));

        $this->assertTrue($result->isOk());

        /** @var array<SongType> $songTypes */
        $songTypes = $this->getAll(FileSongTypeRepository::class);
        $this->assertCount(1, $songTypes);
        $this->assertSame('オリジナル', $songTypes[array_key_first($songTypes)]->songTypeName->value);
        $this->assertSame(1, $songTypes[array_key_first($songTypes)]->orderNo->value);
    }

    #[Test]
    public function createFailsIfNameAlreadyExists(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(1))
        );

        $result = $this->getInstance()->handle(new CreateInputData('オリジナル', 1));

        $this->assertTrue($result->isErr());

        /** @var array<SongType> $songTypes */
        $songTypes = $this->getAll(FileSongTypeRepository::class);
        $this->assertCount(1, $songTypes);
        $this->assertSame($uuid, $songTypes[$uuid]->songTypeId->value);
        $this->assertSame('オリジナル', $songTypes[$uuid]->songTypeName->value);
        $this->assertSame(1, $songTypes[$uuid]->orderNo->value);
    }

    private function getInstance(): CreateInteractor
    {
        return $this->app->make(CreateInteractor::class);
    }
}
