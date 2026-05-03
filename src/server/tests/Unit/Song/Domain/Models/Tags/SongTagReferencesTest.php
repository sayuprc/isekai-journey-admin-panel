<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Tags;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tags\SongTagReferences;
use Tests\TestCase;

class SongTagReferencesTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $input = [
            ['songTagId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['songTagId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'],
        ];

        $result = SongTagReferences::fromArray($input);

        $this->assertTrue($result->isOk());
        $tags = $result->unwrap();

        $this->assertCount(2, $tags);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $tags[0]->songTagId->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $tags[1]->songTagId->value);
    }

    #[Test]
    public function fromArrayFailsWithDuplicateSongTagId(): void
    {
        $input = [
            ['songTagId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['songTagId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
        ];

        $result = SongTagReferences::fromArray($input);

        $this->assertTrue($result->isErr());
        $this->assertSame(
            ['songTagId' => ['同じ楽曲タグを複数指定することはできません。']],
            $result->unwrapErr()->errors,
        );
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $result = SongTagReferences::fromArray([]);

        $this->assertTrue($result->isOk());
        $this->assertCount(0, $result->unwrap());
    }
}
