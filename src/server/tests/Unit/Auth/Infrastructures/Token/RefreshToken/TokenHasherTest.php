<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Token\RefreshToken;

use Auth\Infrastructures\Token\RefreshToken\TokenHasher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenHasherTest extends TestCase
{
    #[Test]
    public function hashCreatesHashedToken(): void
    {
        $hasher = new TokenHasher();
        $plainToken = 'my-plain-token-value-12345';

        $hashedToken = $hasher->hash($plainToken);

        $this->assertNotEquals($plainToken, $hashedToken);
        $this->assertStringStartsWith('$2y$', $hashedToken); // bcrypt format
    }

    #[Test]
    public function verifyReturnsTrueForMatchingToken(): void
    {
        $hasher = new TokenHasher();
        $plainToken = 'my-plain-token-value-12345';
        $hashedToken = $hasher->hash($plainToken);

        $result = $hasher->verify($plainToken, $hashedToken);

        $this->assertTrue($result);
    }

    #[Test]
    public function verifyReturnsFalseForNonMatchingToken(): void
    {
        $hasher = new TokenHasher();
        $plainToken = 'my-plain-token-value-12345';
        $wrongToken = 'different-token-value';
        $hashedToken = $hasher->hash($plainToken);

        $result = $hasher->verify($wrongToken, $hashedToken);

        $this->assertFalse($result);
    }

    #[Test]
    public function hashCreatesUniqueHashesForSameInput(): void
    {
        $hasher = new TokenHasher();
        $plainToken = 'my-plain-token-value-12345';

        $hash1 = $hasher->hash($plainToken);
        $hash2 = $hasher->hash($plainToken);

        // bcrypt includes a salt, so hashes should be different
        $this->assertNotEquals($hash1, $hash2);
        // But both should verify correctly
        $this->assertTrue($hasher->verify($plainToken, $hash1));
        $this->assertTrue($hasher->verify($plainToken, $hash2));
    }
}
