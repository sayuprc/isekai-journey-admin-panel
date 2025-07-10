<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Infrastructures\Credential\RefreshTokenFactory;

class RefreshTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        CarbonImmutable::setTestNow('2019-12-02 12:34:29');

        $now = new CarbonImmutable();

        $refreshToken = $this->getInstance()->create();

        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $refreshToken->expiredAt->value->format('Y-m-d H:i:s'));
    }

    private function getInstance(): RefreshTokenFactory
    {
        return $this->app->make(RefreshTokenFactory::class);
    }
}
