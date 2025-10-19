<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Infrastructures;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Test;
use Support\Infrastructures\Clock;
use Tests\TestCase;

class ClockTest extends TestCase
{
    #[Test]
    public function now(): void
    {
        Chronos::setTestNow('2019-12-09 12:35:40');

        $this->assertSame('2019-12-09 12:35:40', $this->getInstance()->now()->format('Y-m-d H:i:s'));
    }

    private function getInstance(): Clock
    {
        return new Clock();
    }
}
