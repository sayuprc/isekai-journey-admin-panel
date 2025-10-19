<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential\RefreshToken;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;
use User\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;

class RefreshTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        Chronos::setTestNow('2019-12-02 12:34:29');

        $now = new Chronos();

        $userId = $this->generateUuid();

        $refreshToken = $this->getInstance()->create($userId);

        $expiredAtProp = new ReflectionProperty($refreshToken, 'expiredAt');
        $expiredAtProp->setAccessible(true);
        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $expiredAtProp->getValue($refreshToken)->value->format('Y-m-d H:i:s'));
    }

    private function getInstance(): RefreshTokenFactory
    {
        return $this->container->get(RefreshTokenFactory::class);
    }
}
