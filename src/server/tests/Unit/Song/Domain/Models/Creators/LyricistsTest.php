<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Creators;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Creators\Lyricists;
use Tests\TestCase;

class LyricistsTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $input = [
            ['creatorId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'],
        ];

        $result = Lyricists::fromArray($input);

        $this->assertTrue($result->isOk());
        $lyricists = $result->unwrap();

        $this->assertCount(2, $lyricists);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $lyricists[0]->creatorId->value);
        $this->assertSame(1, $lyricists[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $lyricists[1]->creatorId->value);
        $this->assertSame(2, $lyricists[1]->orderNo->value);
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $input = [];

        $result = Lyricists::fromArray($input);

        $this->assertTrue($result->isOk());
        $lyricists = $result->unwrap();

        $this->assertCount(0, $lyricists);
    }
}
