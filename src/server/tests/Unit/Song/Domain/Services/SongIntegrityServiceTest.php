<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
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

    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(SongFactoryInterface::class);
        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $title = '描き続けた君へ';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $songType = SongType::Original->value;
        $orderNo = 1;
        $description = '説明';

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

        $this->creatorRepository->shouldReceive('findByIds')
            ->withArgs(
                fn (
                    CreatorId $arg1,
                    CreatorId $arg2,
                    CreatorId $arg3,
                ): bool => $arg1->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg2->value === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                    && $arg3->value === 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
            )
            ->andReturn([
                $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', ''),
                $this->createCreator('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', ''),
                $this->createCreator('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', ''),
            ])
            ->once();

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->factory->shouldReceive('create')
            ->withArgs(
                // TODO Collection のチェックもしたい
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

    #[Test]
    public function prepareForCreateNotExistsCreator(): void
    {
        $title = '描き続けた君へ';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $songType = SongType::Original->value;
        $orderNo = 1;
        $description = '説明';

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

        $this->creatorRepository->shouldReceive('findByIds')
            ->withArgs(
                fn (
                    CreatorId $arg1,
                    CreatorId $arg2,
                    CreatorId $arg3,
                ): bool => $arg1->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'
                    && $arg2->value === 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'
                    && $arg3->value === 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
            )
            ->andReturn([
                // D のやつがいない場合
                $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', ''),
                $this->createCreator('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', ''),
            ])
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

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): SongIntegrityService
    {
        return new SongIntegrityService(
            $this->generator,
            $this->factory,
            $this->creatorRepository,
        );
    }
}
