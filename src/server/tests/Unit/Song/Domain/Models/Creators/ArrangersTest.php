<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models\Creators;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Creators\Arrangers;
use Tests\TestCase;

class ArrangersTest extends TestCase
{
    #[Test]
    public function fromArray(): void
    {
        $input = [
            ['creatorId' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'],
            ['creatorId' => 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'],
        ];

        $result = Arrangers::fromArray($input);

        $this->assertTrue($result->isOk());
        $arrangers = $result->unwrap();

        $this->assertCount(2, $arrangers);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $arrangers[0]->creatorId->value);
        $this->assertSame(1, $arrangers[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $arrangers[1]->creatorId->value);
        $this->assertSame(2, $arrangers[1]->orderNo->value);
    }

    #[Test]
    public function fromArrayEmpty(): void
    {
        $input = [];

        $result = Arrangers::fromArray($input);

        $this->assertTrue($result->isOk());
        $arrangers = $result->unwrap();

        $this->assertCount(0, $arrangers);
    }

    #[Test]
    public function fromArrayInvalidCreatorId(): void
    {
        $input = [
            ['creatorId' => 'invalid-uuid'],
        ];

        $result = Arrangers::fromArray($input);

        $this->assertTrue($result->isErr());
    }
}
