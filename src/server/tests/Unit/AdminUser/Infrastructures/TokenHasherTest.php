<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Infrastructures;

use AdminUser\Domain\Models\Invitation\PlainToken;
use AdminUser\Infrastructures\TokenHasher;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenHasherTest extends TestCase
{
    #[Test]
    public function sameInputProducesSameOutput(): void
    {
        $hasher = new TokenHasher('secret-key');
        $plain = PlainToken::reconstruct(str_repeat('a', 64));

        $this->assertSame($hasher->hash($plain)->value, $hasher->hash($plain)->value);
    }

    #[Test]
    public function differentSecretsProduceDifferentHashes(): void
    {
        $plain = PlainToken::reconstruct(str_repeat('a', 64));

        $hash1 = new TokenHasher('secret-1')->hash($plain)->value;
        $hash2 = new TokenHasher('secret-2')->hash($plain)->value;

        $this->assertNotSame($hash1, $hash2);
    }

    #[Test]
    public function hashedValueIsNotEqualToPlainToken(): void
    {
        $hasher = new TokenHasher('secret-key');
        $plain = PlainToken::reconstruct(str_repeat('a', 64));

        $this->assertNotSame($plain->value, $hasher->hash($plain)->value);
    }
}
