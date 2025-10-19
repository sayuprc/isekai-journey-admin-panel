<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential\AccessToken;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Tests\TestCase;
use User\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use User\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use User\Domain\Services\Credential\AccessToken\JwtConfig;
use User\Infrastructures\Credential\AccessToken\JwtHandler;

class JwtHandlerTest extends TestCase
{
    private ClockInterface&MockInterface $clock;

    private MapperInterface&MockInterface $mapper;

    private JwtConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->mapper = Mockery::mock(MapperInterface::class);
        $this->config = new JwtConfig(iss: 'iss', alg: 'HS256', key: 'key');
    }

    #[Test]
    public function generateJwtSuccessfully(): void
    {
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
        return new JwtHandler($this->clock, $this->mapper, $this->config);
    }
}
