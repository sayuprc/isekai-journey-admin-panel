<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Credential\RefreshToken;

use Auth\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;

class RefreshTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        CarbonImmutable::setTestNow('2019-12-02 12:34:29');

        $now = new CarbonImmutable();

        $userId = $this->generateUuid();

        $result = $this->getInstance()->create($userId);

        $this->assertTrue($result->isOk());

        $refreshToken = $result->unwrap();

        $expiredAtProp = new ReflectionProperty($refreshToken, 'expiredAt');
        $expiredAtProp->setAccessible(true);
        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $expiredAtProp->getValue($refreshToken)->value->format('Y-m-d H:i:s'));
    }

    private function getInstance(): RefreshTokenFactory
    {
        return $this->app->make(RefreshTokenFactory::class);
    }
}
