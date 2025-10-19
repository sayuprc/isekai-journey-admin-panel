<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential\AccessToken;

use Cake\Chronos\Chronos;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use User\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use User\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;
use User\Domain\Services\Credential\AccessToken\JwtConfig;
use User\Infrastructures\Credential\AccessToken\JwtHandler;

class JwtHandlerTest extends TestCase
{
    #[Test]
    public function generateJwtSuccessfully(): void
    {
        $this->setConfig();

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
    public function verifyJwtSuccessfully(): void
    {
        $this->setConfig();

        Chronos::setTestNow(($now = new DateTimeImmutable())->format('Y-m-d H:i:s'));

        $afterAHour = $now->modify('+1 hours');

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: $iss = $this->container->get(JwtConfig::class)->iss,
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti'
        ));

        $payload = $this->getInstance()->verify($jwt);

        $this->assertSame($iss, $payload->iss);
        $this->assertSame($now->getTimestamp(), $payload->iat);
        $this->assertSame($afterAHour->getTimestamp(), $payload->exp);
        $this->assertSame($now->getTimestamp(), $payload->nbf);
        $this->assertSame('jti', $payload->jti);
    }

    #[Test]
    public function throwExceptionWhenExpireToken(): void
    {
        $this->setConfig();

        $this->expectException(ExpiredException::class);

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti'
        ));

        $this->getInstance()->verify($jwt);
    }

    #[Test]
    public function throwExceptionWhenInvalidIssuer(): void
    {
        $this->setConfig();

        $this->expectException(InvalidIssuerException::class);

        Chronos::setTestNow(($now = new DateTimeImmutable())->format('Y-m-d H:i:s'));

        $afterAHour = $now->modify('+1 hours');

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'invalid iss',
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti'
        ));

        $this->getInstance()->verify($jwt);
    }

    private function setConfig(): void
    {
        $this->container->register(JwtConfig::class, fn () => new JwtConfig(iss: 'iss', alg: 'HS256', key: 'key'));
    }

    private function getInstance(): JwtHandler
    {
        return $this->container->get(JwtHandler::class);
    }
}
