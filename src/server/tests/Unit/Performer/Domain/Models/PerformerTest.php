<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Domain\Models;

use Performer\Domain\Models\Performer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerformerTest extends TestCase
{
    #[Test]
    #[DataProvider('equalsDataProvider')]
    public function equals(Performer $object, Performer $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsDataProvider(): array
    {
        return [
            [
                Performer::reconstruct('11111111-1111-1111-1111-111111111111', 'Performer Name', 1),
                Performer::reconstruct('11111111-1111-1111-1111-111111111111', 'Performer Name', 1),
                true,
            ],
            [
                Performer::reconstruct('11111111-1111-1111-1111-111111111111', 'Performer Name', 1),
                Performer::reconstruct('11111111-1111-1111-1111-111111111111', 'Other Performer', 2),
                true,
            ],
            [
                Performer::reconstruct('11111111-1111-1111-1111-111111111111', 'Performer Name', 1),
                Performer::reconstruct('22222222-2222-2222-2222-222222222222', 'Performer Name', 1),
                false,
            ],
            [
                Performer::reconstruct('11111111-1111-1111-1111-111111111111', 'Performer Name', 1),
                Performer::reconstruct('22222222-2222-2222-2222-222222222222', 'Other Performer', 2),
                false,
            ],
        ];
    }
}
