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
use SongType\Domain\Models\SongType;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class SongAssemblerTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $creatorRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->creatorRepository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function canAssemble(): void
    {
        $uuid = $this->generateUuid();
        $title = '描き続けた君へ';
        $description = 'オリジナル楽曲';
        $songType = SongType::Original;
        $orderNo = 1;

        $song = $this->createSong(
            $uuid,
            $title,
            $description,
            $songType,
            $orderNo,
            [['creatorId' => $lyricistId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $composerId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $arrangerId = $this->generateUuid(), 'orderNo' => 1]],
        );

        foreach ([
            $lyricistId => '作詞者',
            $composerId => '作曲者',
            $arrangerId => '編曲者',
        ] as $id => $name) {
            $this->creatorRepository->shouldReceive('find')
                ->withArgs(fn (CreatorId $arg): bool => $arg->value === $id)
                ->andReturn(new Creator(CreatorId::reconstruct($id), CreatorName::reconstruct($name), OrderNo::reconstruct(1)))
                ->once();
        }

        $assembled = $this->getInstance()->assemble($song);

        $this->assertSame($uuid, $assembled->songId);
        $this->assertSame($title, $assembled->title);
        $this->assertSame($description, $assembled->description);
        $this->assertSame($songType->getName(), $assembled->songTypeName);
        $this->assertSame($songType->value, $assembled->songTypeValue);
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

    private function getInstance(): SongAssembler
    {
        return new SongAssembler($this->creatorRepository);
    }
}
