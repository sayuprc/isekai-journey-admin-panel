<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Infrastructures\Credential\AccessToken\AccessTokenFactory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccessTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        CarbonImmutable::setTestNow('2019-12-09 12:30:20');

        $now = new CarbonImmutable();

        $accessToken = $this->getInstance()->create(
            new AccessTokenPayload(
                'issuer',
                $now->getTimestamp(),
                $now->modify('+1 hours')->getTimestamp(),
                $now->getTimestamp(),
                'id',
            ),
        );

        $elements = explode('.', $accessToken->jwt->value);

        $payload = json_decode(base64_decode($elements[1]));

        $this->assertSame('issuer', $payload->iss);
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
