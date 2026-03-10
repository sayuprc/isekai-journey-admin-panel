<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Services;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Title;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongIntegrityServiceTest extends TestCase
{
    use EntityFactory;

    private MockInterface&UuidGeneratorInterface $generator;

    private MockInterface&SongFactoryInterface $factory;

    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    private MockInterface&SongRepositoryInterface $songRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->factory = Mockery::mock(SongFactoryInterface::class);
        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->songRepository = Mockery::mock(SongRepositoryInterface::class);
    }

    #[Test]
    public function prepareForCreate(): void
    {
        $title = '描き続けた君へ';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $type = SongType::Original->value;
        $attribute = null;
        $currentMaxOrderNo = 100;
        $expectedOrderNo = 110;
        $description = '説明';

        $expectedSong = $this->createSong(
            $uuid,
            $title,
            $description,
            SongType::Original,
            $attribute,
            $expectedOrderNo,
            [['creatorId' => $lyricistId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]],
            [['creatorId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]],
            [['creatorId' => $arrangerId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]],
        );

        $this->creatorRepository->shouldReceive('findByIds')
            ->withArgs(
                fn (
                    CreatorId $arg1,
                    CreatorId $arg2,
                    CreatorId $arg3,
                ): bool => $arg1->value === $lyricistId
                    && $arg2->value === $composerId
                    && $arg3->value === $arrangerId,
            )
            ->andReturn([
                $this->createCreator($lyricistId, '', 1),
                $this->createCreator($composerId, '', 1),
                $this->createCreator($arrangerId, '', 1),
            ])
            ->once();

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn($uuid)
            ->once();

        $this->songRepository->shouldReceive('getMaxOrderNo')
            ->with()
            ->andReturn($currentMaxOrderNo)
            ->once();

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    SongId $songIdArg,
                    Title $titleArg,
                    Description $descriptionArg,
                    SongType $typeArg,
                    ?SongAttribute $attributeArg,
                    OrderNo $orderNoArg,
                    Lyricists $lyricistsArg,
                    Composers $composersArg,
                    Arrangers $arrangersArg,
                ): bool => $songIdArg->value === $uuid
                    && $titleArg->value === $title
                    && $descriptionArg->value === $description
                    && $typeArg->value === $type
                    && $attributeArg === $attribute
                    && $orderNoArg->value === $expectedOrderNo
                    && $lyricistsArg->count() === 1
                    && $lyricistsArg[0]->creatorId->value === $lyricistId
                    && $lyricistsArg[0]->orderNo->value === 1
                    && $composersArg->count() === 1
                    && $composersArg[0]->creatorId->value === $composerId
                    && $composersArg[0]->orderNo->value === 1
                    && $arrangersArg->count() === 1
                    && $arrangersArg[0]->creatorId->value === $arrangerId
                    && $arrangersArg[0]->orderNo->value === 1,
            )
            ->andReturn($expectedSong)
            ->once();

        $result = $this->getInstance()->prepareForCreate(
            $title,
            $description,
            $type,
            $attribute,
            [['creatorId' => $lyricistId]],
            [['creatorId' => $composerId]],
            [['creatorId' => $arrangerId]],
        );

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedSong, $result->unwrap());
    }

    #[Test]
    public function prepareForCreateNotExistsCreator(): void
    {
        $title = '描き続けた君へ';
        $uuid = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $type = SongType::Original->value;
        $currentMaxOrderNo = 100;
        $expectedOrderNo = 110;
        $description = '説明';

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
                $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '', 1),
                $this->createCreator('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', '', 1),
            ])
            ->once();

        $result = $this->getInstance()->prepareForCreate(
            $title,
            $description,
            $type,
            null,
            [['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB']],
            [['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC']],
            [['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD']],
        );

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function prepareForUpdate(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = '描き続けた君へ';
        $description = '説明';
        $type = SongType::Original->value;
        $orderNo = 1;

        $expectedSong = $this->createSong(
            $songId,
            $title,
            $description,
            SongType::Original,
            null,
            $orderNo,
            [['creatorId' => $lyricistId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]],
            [['creatorId' => $composerId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]],
            [['creatorId' => $arrangerId = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]],
        );

        $this->creatorRepository->shouldReceive('findByIds')
            ->withArgs(
                fn (
                    CreatorId $arg1,
                    CreatorId $arg2,
                    CreatorId $arg3,
                ): bool => $arg1->value === $lyricistId
                    && $arg2->value === $composerId
                    && $arg3->value === $arrangerId,
            )
            ->andReturn([
                $this->createCreator($lyricistId, '', 1),
                $this->createCreator($composerId, '', 1),
                $this->createCreator($arrangerId, '', 1),
            ])
            ->once();

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    SongId $songIdArg,
                    Title $titleArg,
                    Description $descriptionArg,
                    SongType $typeArg,
                    ?SongAttribute $attributeArg,
                    OrderNo $orderNoArg,
                    Lyricists $lyricistsArg,
                    Composers $composersArg,
                    Arrangers $arrangersArg,
                ): bool => $songIdArg->value === $songId
                    && $titleArg->value === $title
                    && $descriptionArg->value === $description
                    && $typeArg->value === $type
                    && $attributeArg === null
                    && $orderNoArg->value === $orderNo
                    && $lyricistsArg->count() === 1
                    && $lyricistsArg[0]->creatorId->value === $lyricistId
                    && $lyricistsArg[0]->orderNo->value === 1
                    && $composersArg->count() === 1
                    && $composersArg[0]->creatorId->value === $composerId
                    && $composersArg[0]->orderNo->value === 1
                    && $arrangersArg->count() === 1
                    && $arrangersArg[0]->creatorId->value === $arrangerId
                    && $arrangersArg[0]->orderNo->value === 1,
            )
            ->andReturn($expectedSong)
            ->once();

        $result = $this->getInstance()->prepareForUpdate(
            $songId,
            $title,
            $description,
            $type,
            null,
            $orderNo,
            [['creatorId' => $lyricistId]],
            [['creatorId' => $composerId]],
            [['creatorId' => $arrangerId]],
        );

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedSong, $result->unwrap());
    }

    #[Test]
    public function prepareForUpdateNotExistsCreator(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $title = '描き続けた君へ';
        $description = '説明';
        $type = SongType::Original->value;
        $orderNo = 1;

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
                $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '', 1),
                $this->createCreator('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', '', 1),
            ])
            ->once();

        $result = $this->getInstance()->prepareForUpdate(
            $songId,
            $title,
            $description,
            $type,
            null,
            $orderNo,
            [['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB']],
            [['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC']],
            [['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD']],
        );

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): SongIntegrityService
    {
        return new SongIntegrityService(
            $this->generator,
            $this->factory,
            $this->songRepository,
            $this->creatorRepository,
        );
    }
}
