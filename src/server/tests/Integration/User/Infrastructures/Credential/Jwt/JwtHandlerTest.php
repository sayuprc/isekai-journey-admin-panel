<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential\Jwt;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Domain\Services\Jwt\AccessTokenPayload;
use User\Domain\Services\Jwt\Exceptions\ExpiredException;
use User\Infrastructures\Credential\Jwt\JwtHandler;

class JwtHandlerTest extends TestCase
{
    #[Test]
    public function generateJwtSuccessfully(): void
    {
        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 180,
            jti: 'jti'
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
    public function decodeJwtSuccessfully(): void
    {
        CarbonImmutable::setTestNow($now = new DateTimeImmutable());

        $afterAHour = $now->modify('+1 hours');

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti'
        ));

        $payload = $this->getInstance()->decode($jwt);

        $this->assertSame('iss', $payload->iss);
        $this->assertSame($now->getTimestamp(), $payload->iat);
        $this->assertSame($afterAHour->getTimestamp(), $payload->exp);
        $this->assertSame($now->getTimestamp(), $payload->nbf);
        $this->assertSame('jti', $payload->jti);
    }

    #[Test]
    public function throwExceptionWhenExpireToken(): void
    {
        $this->expectException(ExpiredException::class);

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti'
        ));

        $this->getInstance()->decode($jwt);
    }

    private function getInstance(): JwtHandler
    {
        return $this->app->make(JwtHandler::class);
    }
}
