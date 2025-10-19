<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use SongType\Application\Interactors\ListInteractor;
use SongType\Application\UseCase\List\ListOutputData;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function emptySongTypes(): void
    {
        $response = $this->getInstance()->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->songTypes);
    }

    #[Test]
    public function nonEmptySongTypes(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(1))
        );

        $response = $this->getInstance()->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(1, $response->songTypes);

        $this->assertSame($uuid, $response->songTypes[0]->songTypeId->value);
        $this->assertSame('オリジナル', $response->songTypes[0]->songTypeName->value);
        $this->assertSame(1, $response->songTypes[0]->orderNo->value);
    }

    private function getInstance(): ListInteractor
    {
        return $this->container->get(ListInteractor::class);
    }
}
