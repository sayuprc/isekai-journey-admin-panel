<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use Auth\Infrastructures\Credential\AccessToken\JwtHandler;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JwtHandlerTest extends TestCase
{
    #[Test]
    public function generateJwtSuccessfully(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 180,
            jti: 'jti',
        ));

        $elements = explode('.', $jwt);
        $payload = json_decode(base64_decode($elements[1]), true);

        $this->assertSame([
            'iss' => 'iss',
            'iat' => 0,
            'exp' => 180,
            'nbf' => 180,
            'jti' => 'jti',
        ], $payload);
    }

    #[Test]
    public function verifyJwtSuccessfully(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        CarbonImmutable::setTestNow($now = new DateTimeImmutable());

        $afterAHour = $now->modify('+1 hours');

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: $iss = $this->app->make(JwtConfigInterface::class)->issuer(),
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti',
        ));

        $result = $this->getInstance()->verify($jwt);

        $this->assertTrue($result->isOk());

        $payload = $result->unwrap();

        $this->assertSame($iss, $payload->iss);
        $this->assertSame($now->getTimestamp(), $payload->iat);
        $this->assertSame($afterAHour->getTimestamp(), $payload->exp);
        $this->assertSame($now->getTimestamp(), $payload->nbf);
        $this->assertSame('jti', $payload->jti);
    }

    #[Test]
    public function throwExceptionWhenExpireToken(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti',
        ));

        $result = $this->getInstance()->verify($jwt);

        $this->assertFalse($result->isOk());
    }

    #[Test]
    public function throwExceptionWhenInvalidIssuer(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        CarbonImmutable::setTestNow($now = new DateTimeImmutable());

        $afterAHour = $now->modify('+1 hours');

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti',
        ));

        $result = $this->getInstance()->verify($jwt);

        $this->assertFalse($result->isOk());
    }

    private function getInstance(): JwtHandler
    {
        return $this->app->make(JwtHandler::class);
    }
}
