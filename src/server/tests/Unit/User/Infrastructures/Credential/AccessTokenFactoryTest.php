<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Tests\TestCase;
use User\Domain\Services\Jwt\JwtHandlerInterface;
use User\Infrastructures\Credential\AccessTokenFactory;
use User\Infrastructures\Credential\Jwt\AccessTokenPayload;

class AccessTokenFactoryTest extends TestCase
{
    private ConfigInterface&MockInterface $config;

    private ClockInterface&MockInterface $clock;

    private JwtHandlerInterface&MockInterface $jwt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(ConfigInterface::class);
        $this->clock = Mockery::mock(ClockInterface::class);
        $this->jwt = Mockery::mock(JwtHandlerInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now = new DateTimeImmutable())
            ->once();

        $this->config->shouldReceive('getString')
            ->with('app.url')
            ->andReturn('https://example.com')
            ->once();

        $this->jwt->shouldReceive('generate')
            ->with(Mockery::on(function (AccessTokenPayload $arg) use ($now) {
                $payload = $arg->toArray();

                return $payload['iss'] === 'https://example.com'
                    && $payload['iat'] === $now->getTimestamp()
                    && $payload['exp'] === $now->modify('+1 hours')->getTimestamp()
                    && $payload['nbf'] === $now->getTimestamp()
                    && $payload['jti'] === 'id';
            }))
            ->andReturn('jwt')
            ->once();

        $this->getInstance()->create('id');
    }

    private function getInstance(): AccessTokenFactory
    {
        return new AccessTokenFactory($this->config, $this->clock, $this->jwt);
    }
}
