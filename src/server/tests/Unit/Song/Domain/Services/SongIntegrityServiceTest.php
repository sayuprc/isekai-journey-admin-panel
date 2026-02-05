<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Description;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use Song\Domain\Services\SongIntegrityService;
use SongType\Domain\Models\SongType;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&SongFactoryInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(SongFactoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $title = '描き続けた君へ';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $songType = SongType::Original->value;
        $orderNo = 1;
        $description = '説明';

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $expectedSong = $this->createSong(
            $uuid,
            $title,
            $description,
            SongType::Original,
            $orderNo,
            [['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]],
            [['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]],
            [['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]],
        );

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    SongId $songIdArg,
                    Title $titleArg,
                    Description $descriptionArg,
                    SongType $songTypeArg,
                    OrderNo $orderNoArg,
                ): bool => $songIdArg->value === $uuid
                    && $titleArg->value === $title
                    && $descriptionArg->value === $description
                    && $songTypeArg->value === $songType
                    && $orderNoArg->value === $orderNo,
            )
            ->andReturn($expectedSong)
            ->once();

        $result = $this->getInstance()->prepareForCreate(
            $title,
            $description,
            $songType,
            $orderNo,
            [['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]],
            [['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]],
            [['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]],
        );

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedSong, $result->unwrap());
    }

    private function getInstance(): SongIntegrityService
    {
        return new SongIntegrityService(
            $this->generator,
            $this->factory,
        );
    }
}
