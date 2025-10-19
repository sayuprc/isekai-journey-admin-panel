<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Infrastructures\Hasher;

class HasherTest extends TestCase
{
    #[Test]
    public function hash(): void
    {
        $hashedValue = $this->getInstance()->hash('plain');

        $this->assertNotSame('plain', $hashedValue);
    }

    private function getInstance(): Hasher
    {
        return new Hasher();
    }
}
