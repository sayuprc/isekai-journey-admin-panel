<?php

declare(strict_types=1);

namespace Shared\Application\Uuid;

use PHPUnit\Framework\Attributes\Test;
use Shared\Uuid\UuidGeneratorInterface;
use Tests\TestCase;

class DummyUuidGeneratorTest extends TestCase
{
    #[Test]
    public function generateDummy(): void
    {
        $generator = new DummyUuidGenerator();

        $this->assertInstanceOf(UuidGeneratorInterface::class, $generator);
        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $generator->generate());
    }
}
