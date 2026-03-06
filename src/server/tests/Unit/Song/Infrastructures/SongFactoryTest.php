<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use Song\Infrastructures\SongFactory;
use SongType\Domain\Models\SongType;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class SongFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $song = $this->getInstance()->create(
            SongId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            Title::reconstruct('描き続けた君へ'),
            Description::reconstruct('オリジナル楽曲'),
            SongType::Original,
            null,
            OrderNo::reconstruct(1),
            Lyricists::fromArray([['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'orderNo' => 1]])->unwrap(),
            Composers::fromArray([['creatorId' => 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', 'orderNo' => 1]])->unwrap(),
            Arrangers::fromArray([['creatorId' => 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', 'orderNo' => 1]])->unwrap(),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $song->songId->value);
        $this->assertSame('描き続けた君へ', $song->title->value);
        $this->assertSame('オリジナル楽曲', $song->description->value);
        $this->assertSame(SongType::Original, $song->songType);
        $this->assertSame(1, $song->orderNo->value);
        $this->assertCount(1, $song->lyricists);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $song->lyricists[0]->creatorId->value);
        $this->assertSame(1, $song->lyricists[0]->orderNo->value);
        $this->assertCount(1, $song->composers);
        $this->assertSame('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $song->composers[0]->creatorId->value);
        $this->assertSame(1, $song->composers[0]->orderNo->value);
        $this->assertCount(1, $song->arrangers);
        $this->assertSame('DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD', $song->arrangers[0]->creatorId->value);
        $this->assertSame(1, $song->arrangers[0]->orderNo->value);
    }

    private function getInstance(): SongFactory
    {
        return new SongFactory();
    }
}
