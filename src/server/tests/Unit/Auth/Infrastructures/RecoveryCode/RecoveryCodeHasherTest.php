<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\RecoveryCode;

use Auth\Infrastructures\RecoveryCode\RecoveryCodeHasher;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecoveryCodeHasherTest extends TestCase
{
    private RecoveryCodeHasher $hasher;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = new RecoveryCodeHasher();
    }

    #[Test]
    public function hashCreatesHashedCode(): void
    {
        $plainCode = 'A3KP-9QXR';

        $hashedCode = $this->hasher->hash($plainCode);

        $this->assertNotEquals($plainCode, $hashedCode);
        $this->assertSame(hash('sha256', $plainCode), $hashedCode);
    }

    #[Test]
    public function hashIsDeterministic(): void
    {
        $plainCode = 'A3KP-9QXR';

        $this->assertSame($this->hasher->hash($plainCode), $this->hasher->hash($plainCode));
    }

    #[Test]
    public function verifyReturnsTrueForMatchingCode(): void
    {
        $plainCode = 'A3KP-9QXR';
        $hashedCode = $this->hasher->hash($plainCode);

        $this->assertTrue($this->hasher->verify($plainCode, $hashedCode));
    }

    #[Test]
    public function verifyReturnsFalseForNonMatchingCode(): void
    {
        $hashedCode = $this->hasher->hash('A3KP-9QXR');

        $this->assertFalse($this->hasher->verify('ZZZZ-ZZZZ', $hashedCode));
    }
}
