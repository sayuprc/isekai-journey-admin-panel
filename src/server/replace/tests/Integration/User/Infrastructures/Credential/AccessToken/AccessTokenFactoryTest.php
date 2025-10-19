<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential\AccessToken;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Domain\Services\Credential\AccessToken\JwtConfig;
use User\Infrastructures\Credential\AccessToken\AccessTokenFactory;

class AccessTokenFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        $this->setConfig();

        Chronos::setTestNow('2019-12-09 12:30:20');

        $now = new Chronos();

        $accessToken = $this->getInstance()->create('id');

        $elements = explode('.', $accessToken->jwt->value);

        $payload = json_decode(base64_decode($elements[1]));

        $this->assertSame('iss', $payload->iss);
        $this->assertSame($now->getTimestamp(), $payload->iat);
        $this->assertSame($now->modify('+1 hours')->getTimestamp(), $payload->exp);
        $this->assertSame($now->getTimestamp(), $payload->nbf);
        $this->assertSame('id', $payload->jti);
    }

    private function setConfig(): void
    {
        $this->container->register(JwtConfig::class, fn () => new JwtConfig(iss: 'iss', alg: 'HS256', key: 'key'));
    }

    private function getInstance(): AccessTokenFactory
    {
        return $this->container->get(AccessTokenFactory::class);
    }
}
