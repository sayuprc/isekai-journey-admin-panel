<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Token\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\TokenValue;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenFactory;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RefreshTokenFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function createSuccessfully(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        $refreshToken = $this->getInstance()->create(
            RefreshTokenId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            AdminUserId::reconstruct('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            TokenValue::reconstruct('aaaaaaaaaa'),
            ExpiredAt::reconstruct(now()->toDateTimeImmutable()),
            ConsumptionStatus::Unused,
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $refreshToken->refreshTokenId->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $refreshToken->userId->value);
        $this->assertSame('aaaaaaaaaa', $refreshToken->token->value);
        $this->assertTrue($refreshToken->isAvailable(now()->subMinutes(30)));
    }

    private function getInstance(): RefreshTokenFactory
    {
        return new RefreshTokenFactory();
    }
}
