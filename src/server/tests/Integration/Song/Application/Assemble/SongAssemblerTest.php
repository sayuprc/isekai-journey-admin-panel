<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Assemble;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\SongAssembler;
use SongType\Domain\Models\SongType;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class SongAssemblerTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

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
            [['creatorId' => $arrangerId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $composerId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $lyricistId = $this->generateUuid(), 'orderNo' => 1]],
        );

        $this->storeCreators(
            $this->createCreator($arrangerId, '編曲者'),
            $this->createCreator($composerId, '作曲者'),
            $this->createCreator($lyricistId, '作詞者'),
        );

        $assembled = $this->getInstance()->assemble($song);

        $this->assertSame($uuid, $assembled->songId);
        $this->assertSame($title, $assembled->title);
        $this->assertSame($description, $assembled->description);
        $this->assertSame($songType->getName(), $assembled->songTypeName);
        $this->assertSame($songType->value, $assembled->songTypeValue);
        $this->assertSame($orderNo, $assembled->orderNo);
        $this->assertCount(1, $assembled->arrangers);
        $this->assertSame($arrangerId, $assembled->arrangers[0]->creatorId);
        $this->assertSame('編曲者', $assembled->arrangers[0]->creatorName);
        $this->assertSame(1, $assembled->arrangers[0]->orderNo);
        $this->assertCount(1, $assembled->composers);
        $this->assertSame($composerId, $assembled->composers[0]->creatorId);
        $this->assertSame('作曲者', $assembled->composers[0]->creatorName);
        $this->assertSame(1, $assembled->composers[0]->orderNo);
        $this->assertCount(1, $assembled->lyricists);
        $this->assertSame($lyricistId, $assembled->lyricists[0]->creatorId);
        $this->assertSame('作詞者', $assembled->lyricists[0]->creatorName);
        $this->assertSame(1, $assembled->lyricists[0]->orderNo);
    }

    private function getInstance(): SongAssembler
    {
        return $this->app->make(SongAssembler::class);
    }
}
