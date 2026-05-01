<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Assemble;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\SongAssembler;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongAssemblerTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    private MockInterface&SongTagRepositoryInterface $songTagRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
        $this->songTagRepository = Mockery::mock(SongTagRepositoryInterface::class);
    }

    #[Test]
    public function canAssemble(): void
    {
        $uuid = $this->generateUuid();
        $title = '描き続けた君へ';
        $description = 'オリジナル楽曲';
        $type = SongType::Original;
        $orderNo = 1;

        $song = $this->createSong(
            $uuid,
            $title,
            $description,
            $type,
            null,
            $orderNo,
            [['creatorId' => $lyricistId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $composerId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $arrangerId = $this->generateUuid(), 'orderNo' => 1]],
        );

        $this->creatorRepository->shouldReceive('findByIds')
            ->withArgs(function (CreatorId ...$ids) use ($lyricistId, $composerId, $arrangerId): bool {
                $idValues = array_map(fn (CreatorId $id): string => $id->value, $ids);
                sort($idValues);
                $expectedIds = [$lyricistId, $composerId, $arrangerId];
                sort($expectedIds);

                return $idValues === $expectedIds;
            })
            ->andReturn([
                new Creator(CreatorId::reconstruct($lyricistId), CreatorName::reconstruct('作詞者'), OrderNo::reconstruct(1)),
                new Creator(CreatorId::reconstruct($composerId), CreatorName::reconstruct('作曲者'), OrderNo::reconstruct(1)),
                new Creator(CreatorId::reconstruct($arrangerId), CreatorName::reconstruct('編曲者'), OrderNo::reconstruct(1)),
            ])
            ->once();

        $assembled = $this->getInstance()->assemble($song);

        $this->assertSame($uuid, $assembled->songId);
        $this->assertSame($title, $assembled->title);
        $this->assertSame($description, $assembled->description);
        $this->assertSame($type->getName(), $assembled->typeName);
        $this->assertSame($type->value, $assembled->typeValue);
        $this->assertSame($orderNo, $assembled->orderNo);
        $this->assertCount(1, $assembled->lyricists);
        $this->assertSame($lyricistId, $assembled->lyricists[0]->creatorId);
        $this->assertSame('作詞者', $assembled->lyricists[0]->name);
        $this->assertSame(1, $assembled->lyricists[0]->orderNo);
        $this->assertCount(1, $assembled->composers);
        $this->assertSame($composerId, $assembled->composers[0]->creatorId);
        $this->assertSame('作曲者', $assembled->composers[0]->name);
        $this->assertSame(1, $assembled->composers[0]->orderNo);
        $this->assertCount(1, $assembled->arrangers);
        $this->assertSame($arrangerId, $assembled->arrangers[0]->creatorId);
        $this->assertSame('編曲者', $assembled->arrangers[0]->name);
        $this->assertSame(1, $assembled->arrangers[0]->orderNo);
    }

    #[Test]
    public function canAssembleWithoutCreators(): void
    {
        $uuid = $this->generateUuid();
        $title = 'インストゥルメンタル';
        $description = 'クリエイター無しの楽曲';
        $type = SongType::Original;
        $orderNo = 1;

        $song = $this->createSong(
            $uuid,
            $title,
            $description,
            $type,
            null,
            $orderNo,
            [],
            [],
            [],
        );

        $this->creatorRepository->shouldReceive('findByIds')->never();

        $assembled = $this->getInstance()->assemble($song);

        $this->assertSame($uuid, $assembled->songId);
        $this->assertSame($title, $assembled->title);
        $this->assertSame($description, $assembled->description);
        $this->assertSame($type->getName(), $assembled->typeName);
        $this->assertSame($type->value, $assembled->typeValue);
        $this->assertSame($orderNo, $assembled->orderNo);
        $this->assertCount(0, $assembled->lyricists);
        $this->assertCount(0, $assembled->composers);
        $this->assertCount(0, $assembled->arrangers);
    }

    private function getInstance(): SongAssembler
    {
        return new SongAssembler($this->creatorRepository, $this->songTagRepository);
    }
}
