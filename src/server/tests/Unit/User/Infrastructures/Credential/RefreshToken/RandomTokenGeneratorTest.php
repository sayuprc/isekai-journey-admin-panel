<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential\RefreshToken;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Infrastructures\Credential\RefreshToken\RandomTokenGenerator;

class RandomTokenGeneratorTest extends TestCase
{
    #[Test]
    public function random(): void
    {
        $token = $this->getInstance()->generate();

        $this->assertSame(128, mb_strlen($token));
    }

    private function getInstance(): RandomTokenGenerator
    {
        return new RandomTokenGenerator();
    }
}
