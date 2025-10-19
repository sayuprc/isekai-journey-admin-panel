<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Support\Infrastructures\UuidGenerator;
use Tests\TestCase;

class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(UuidGeneratorInterface::class, new UuidGenerator());
    }
}
