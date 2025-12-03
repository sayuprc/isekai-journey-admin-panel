<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Credential\AccessToken;

use Auth\Infrastructures\Credential\AccessToken\AccessTokenFactory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccessTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        CarbonImmutable::setTestNow('2019-12-09 12:30:20');

        $now = new CarbonImmutable();

        $accessToken = $this->getInstance()->create('id');

        $elements = explode('.', $accessToken->jwt->value);

        $payload = json_decode(base64_decode($elements[1]));

        $this->assertSame(config('app.url'), $payload->iss);
        $this->assertSame($now->getTimestamp(), $payload->iat);
        $this->assertSame($now->modify('+1 hours')->getTimestamp(), $payload->exp);
        $this->assertSame($now->getTimestamp(), $payload->nbf);
        $this->assertSame('id', $payload->jti);
    }

    private function getInstance(): AccessTokenFactory
    {
        return $this->app->make(AccessTokenFactory::class);
    }
}
