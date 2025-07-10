<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential\Jwt;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use Tests\TestCase;
use User\Domain\Services\Jwt\AccessTokenPayload;
use User\Domain\Services\Jwt\Exceptions\ExpiredException;
use User\Domain\Services\Jwt\JwtConfigInterface;
use User\Infrastructures\Credential\Jwt\JwtHandler;

class JwtHandlerTest extends TestCase
{
    private ClockInterface&MockInterface $clock;

    private MapperInterface&MockInterface $mapper;

    private JwtConfigInterface&MockInterface $jwtConfig;

    private ConfigInterface&MockInterface $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->mapper = Mockery::mock(MapperInterface::class);
        $this->jwtConfig = Mockery::mock(JwtConfigInterface::class);
        $this->config = Mockery::mock(ConfigInterface::class);
    }

    #[Test]
    public function generateJwtSuccessfully(): void
    {
        $this->config->shouldReceive('getString')
            ->with('auth.jwt.alg')
            ->andReturn('HS256')
            ->once();

        $this->config->shouldReceive('getString')
            ->with('auth.jwt.key')
            ->andReturn('key')
            ->once();

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti'
        ));

        $elements = explode('.', $jwt);
        $payload = json_decode(base64_decode($elements[1]), true);

        $this->assertSame([
            'iss' => 'iss',
            'iat' => 0,
            'exp' => 180,
            'nbf' => 0,
            'jti' => 'jti',
        ], $payload);
    }

    #[Test]
    public function verifyJwtSuccessfully(): void
    {
        $now = new DateTimeImmutable();
        $afterAHour = $now->modify('+1 hours');

        $this->config->shouldReceive('getString')
            ->with('auth.jwt.alg')
            ->andReturn('HS256')
            ->once();

        $this->config->shouldReceive('getString')
            ->with('auth.jwt.key')
            ->andReturn('key')
            ->once();

        $handler = $this->getInstance();

        $jwt = $handler->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: $now->getTimestamp(),
            exp: $afterAHour->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: 'jti'
        ));

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $this->mapper->shouldReceive('map')
            ->with(AccessTokenPayload::class, Mockery::on(fn (stdClass $_) => true))
            ->andReturn(new AccessTokenPayload(
                'iss',
                $now->getTimestamp(),
                $afterAHour->getTimestamp(),
                $now->getTimestamp(),
                'jti',
            ))
            ->once();

        $this->jwtConfig->shouldReceive('issuer')
            ->with()
            ->andReturn('iss')
            ->once();

        $payload = $handler->verify($jwt);

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

        $this->config->shouldReceive('getString')
            ->with('auth.jwt.alg')
            ->andReturn('HS256')
            ->once();

        $this->config->shouldReceive('getString')
            ->with('auth.jwt.key')
            ->andReturn('key')
            ->once();

        $handler = $this->getInstance();

        $jwt = $handler->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 0,
            jti: 'jti'
        ));

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn(new DateTimeImmutable())
            ->once();

        $handler->verify($jwt);
    }

    private function getInstance(): JwtHandler
    {
        return new JwtHandler($this->clock, $this->mapper, $this->jwtConfig, $this->config);
    }
}
