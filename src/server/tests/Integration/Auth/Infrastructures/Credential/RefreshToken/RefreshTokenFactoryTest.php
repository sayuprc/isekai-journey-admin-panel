<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Credential\RefreshToken;

use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Auth\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;
use User\Domain\Models\UserId;

class RefreshTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        CarbonImmutable::setTestNow('2019-12-02 12:34:29');

        $now = new CarbonImmutable();

        $refreshToken = $this->getInstance()->create(
            RefreshTokenId::reconstruct($this->generateUuid()),
            UserId::reconstruct($this->generateUuid()),
            TokenValue::reconstruct('token'),
            ExpiredAt::reconstruct($now->modify('+7 days')),
            ConsumptionStatus::Unused,
        );

        $expiredAtProp = new ReflectionProperty($refreshToken, 'expiredAt');
        $expiredAtProp->setAccessible(true);
        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $expiredAtProp->getValue($refreshToken)->value->format('Y-m-d H:i:s'));
    }

    private function getInstance(): RefreshTokenFactory
    {
        return $this->app->make(RefreshTokenFactory::class);
    }
}
