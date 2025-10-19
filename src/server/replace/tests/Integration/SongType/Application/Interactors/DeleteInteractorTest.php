<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\DeleteInteractor;
use SongType\Application\UseCase\Delete\DeleteInputData;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class DeleteInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(1))
        );

        $this->getInstance()->handle(new DeleteInputData($uuid));

        /** @var array<SongType> $songTypes */
        $songTypes = $this->getAll(FileSongTypeRepository::class);
        $this->assertCount(0, $songTypes);
    }

    private function getInstance(): DeleteInteractor
    {
        return $this->container->get(DeleteInteractor::class);
    }
}
