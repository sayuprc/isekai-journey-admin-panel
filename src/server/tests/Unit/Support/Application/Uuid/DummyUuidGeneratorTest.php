<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Application\Uuid;

use PHPUnit\Framework\Attributes\Test;
use Support\Application\Uuid\DummyUuidGenerator;
use Support\Uuid\UuidGeneratorInterface;
use Tests\TestCase;

class DummyUuidGeneratorTest extends TestCase
{
    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(UuidGeneratorInterface::class, new DummyUuidGenerator());
    }

    #[Test]
    public function generateDummyValue(): void
    {
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', new DummyUuidGenerator()->generate());
    }
}
