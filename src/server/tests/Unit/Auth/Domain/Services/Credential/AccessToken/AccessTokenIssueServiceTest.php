<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services\Credential\AccessToken;

use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AccessTokenIssueServiceTest extends TestCase
{
    use EntityFactory;

    private ClockInterface&MockInterface $clock;

    private JwtConfigInterface&MockInterface $jwtConfig;

    private AccessTokenFactoryInterface&MockInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->jwtConfig = Mockery::mock(JwtConfigInterface::class);
        $this->factory = Mockery::mock(AccessTokenFactoryInterface::class);
    }

    #[Test]
    public function issue(): void
    {
        $id = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $issuer = 'test-issuer';
        $now = new DateTimeImmutable('2026-02-01 00:00:00');

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $this->jwtConfig->shouldReceive('issuer')
            ->with()
            ->andReturn($issuer)
            ->once();

        $expectedAccessToken = $this->createAccessToken('jwt-token');

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (AccessTokenPayload $payload): bool => $payload->iss === $issuer
                    && $payload->iat === $now->getTimestamp()
                    && $payload->exp === $now->modify('+1 hours')->getTimestamp()
                    && $payload->nbf === $now->getTimestamp()
                    && $payload->jti === $id,
            )
            ->andReturn($expectedAccessToken)
            ->once();

        $actual = $this->getInstance()->issue($id);

        $this->assertSame($expectedAccessToken, $actual);
    }

    private function getInstance(): AccessTokenIssueService
    {
        return new AccessTokenIssueService(
            $this->clock,
            $this->jwtConfig,
            $this->factory,
        );
    }
}
