<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Creators;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Creators\Composers;
use Tests\TestCase;

class ComposersTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $input = [
            ['creatorId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'],
        ];

        $result = Composers::fromArray($input);

        $this->assertTrue($result->isOk());
        $composers = $result->unwrap();

        $this->assertCount(2, $composers);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $composers[0]->creatorId->value);
        $this->assertSame(1, $composers[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $composers[1]->creatorId->value);
        $this->assertSame(2, $composers[1]->orderNo->value);
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $input = [];

        $result = Composers::fromArray($input);

        $this->assertTrue($result->isOk());
        $composers = $result->unwrap();

        $this->assertCount(0, $composers);
    }

    #[Test]
    public function toArray(): void
    {
        $input = [
            ['creatorId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'],
        ];

        $composers = Composers::fromArray($input)->unwrap();

        $expected = [
            ['creator_id' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'order_no' => 1],
            ['creator_id' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'order_no' => 2],
        ];

        $this->assertSame($expected, $composers->toArray());
    }
}
