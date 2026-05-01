<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Assemble;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\SongAssembler;
use Song\Domain\Models\SongType;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SongAssemblerTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

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
            $orderNo,
            [['creatorId' => $lyricistId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $composerId = $this->generateUuid(), 'orderNo' => 1]],
            [['creatorId' => $arrangerId = $this->generateUuid(), 'orderNo' => 1]],
        );

        $this->storeCreators(
            $this->createCreator($lyricistId, '作詞者', 1),
            $this->createCreator($composerId, '作曲者', 1),
            $this->createCreator($arrangerId, '編曲者', 1),
        );

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

    private function getInstance(): SongAssembler
    {
        return $this->app->make(SongAssembler::class);
    }
}
