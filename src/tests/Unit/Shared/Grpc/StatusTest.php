<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Grpc;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Shared\Grpc\Status;
use Tests\TestCase;

class StatusTest extends TestCase
{
    #[Test]
    #[DataProvider('validData')]
    public function canBeInstanced(int $code, string $details): void
    {
        $status = new Status($code, $details);

        $this->assertSame($code, $status->code);
        $this->assertSame($details, $status->details);
    }

    public static function validData(): array
    {
        return [
            [0, ''],
            [1, 'error'],
        ];
    }

    #[Test]
    #[DataProvider('isOkData')]
    public function isOk(int $code, string $details, bool $expected): void
    {
        $status = new Status($code, $details);

        $this->assertSame($expected, $status->isOk());
    }

    public static function isOkData(): array
    {
        return [
            [0, '', true],
            [1, 'error', false],
        ];
    }
}
