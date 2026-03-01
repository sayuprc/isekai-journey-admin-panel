<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Domain\Models;

use Creator\Domain\Models\Creator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatorTest extends TestCase
{
    #[Test]
    #[DataProvider('equalsDataProvider')]
    public function equals(Creator $object, Creator $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsDataProvider(): array
    {
        return [
            [
                Creator::reconstruct('11111111-1111-1111-1111-111111111111', 'Creator Name', 1),
                Creator::reconstruct('11111111-1111-1111-1111-111111111111', 'Creator Name', 1),
                true,
            ],
            [
                Creator::reconstruct('11111111-1111-1111-1111-111111111111', 'Creator Name', 1),
                Creator::reconstruct('11111111-1111-1111-1111-111111111111', 'Other Name', 2),
                true,
            ],
            [
                Creator::reconstruct('11111111-1111-1111-1111-111111111111', 'Creator Name', 1),
                Creator::reconstruct('22222222-2222-2222-2222-222222222222', 'Creator Name', 1),
                false,
            ],
            [
                Creator::reconstruct('11111111-1111-1111-1111-111111111111', 'Creator Name', 1),
                Creator::reconstruct('22222222-2222-2222-2222-222222222222', 'Other Name', 2),
                false,
            ],
        ];
    }
}
