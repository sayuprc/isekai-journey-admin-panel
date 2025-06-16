<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Application;

use PHPUnit\Framework\Attributes\Test;
use Support\Application\UuidGenerator;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;

class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(UuidGeneratorInterface::class, new UuidGenerator());
    }
}
