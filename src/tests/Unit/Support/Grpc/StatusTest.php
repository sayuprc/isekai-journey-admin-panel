<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Grpc;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Grpc\Status;
use Tests\TestCase;

class StatusTest extends TestCase
{
    #[Test]
    public function isOk(): void
    {
        $status = new Status(0, '');

        $this->assertTrue($status->isOk());
    }

    #[Test]
    #[DataProvider('provideIsNotOk')]
    public function isNotOk(int $code, string $details): void
    {
        $status = new Status($code, $details);

        $this->assertFalse($status->isOk());
    }

    public static function provideIsNotOk(): array
    {
        return [
            [1, 'error'],
            [2, 'error'],
            [3, 'error'],
        ];
    }
}
