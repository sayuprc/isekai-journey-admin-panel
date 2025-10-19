<?php

declare(strict_types=1);

namespace Tests\Integration\SongType\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use ResultType\Result;
use SongType\Application\Interactors\GetInteractor;
use SongType\Application\UseCase\Get\GetInputData;
use SongType\Application\UseCase\Get\GetOutputData;
use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function getSongType(): void
    {
        $uuid = $this->generateUuid();

        $this->factory(
            FileSongTypeRepository::class,
            $uuid,
            new SongType(new SongTypeId($uuid), new SongTypeName('オリジナル'), new OrderNo(1))
        );

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertInstanceOf(GetOutputData::class, $response);

        $this->assertInstanceOf(SongType::class, $response->songType);
        $this->assertSame($uuid, $response->songType->songTypeId->value);
        $this->assertSame('オリジナル', $response->songType->songTypeName->value);
        $this->assertSame(1, $response->songType->orderNo->value);
    }

    #[Test]
    public function failureGetSongType(): void
    {
        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isErr());

        $this->assertSame('Song type not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }

    private function getInstance(): GetInteractor
    {
        return $this->container->get(GetInteractor::class);
    }
}
