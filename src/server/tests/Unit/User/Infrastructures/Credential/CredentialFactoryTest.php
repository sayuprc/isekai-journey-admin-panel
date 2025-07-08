<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;
use User\Domain\Models\Credential\AccessToken;
use User\Domain\Models\Credential\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\ExpiredAt;
use User\Domain\Models\Credential\IsEnabled;
use User\Domain\Models\Credential\Jwt;
use User\Domain\Models\Credential\RefreshToken;
use User\Domain\Models\Credential\RefreshTokenFactoryInterface;
use User\Domain\Models\Credential\TokenValue;
use User\Infrastructures\Credential\CredentialFactory;

class CredentialFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $uuid;

    private AccessTokenFactoryInterface&MockInterface $accessTokenFactory;

    private MockInterface&RefreshTokenFactoryInterface $refreshTokenFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);
        $this->accessTokenFactory = Mockery::mock(AccessTokenFactoryInterface::class);
        $this->refreshTokenFactory = Mockery::mock(RefreshTokenFactoryInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        $userId = $this->generateUuid();

        $this->uuid->shouldReceive('generate')
            ->with()
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->accessTokenFactory->shouldReceive('create')
            ->with('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(new AccessToken(new Jwt('jwt')))
            ->once();

        $this->refreshTokenFactory->shouldReceive('create')
            ->with()
            ->andReturn(
                new RefreshToken(
                    new TokenValue('token'),
                    new ExpiredAt($now = new DateTimeImmutable()),
                    new IsEnabled(true)
                )
            )
            ->once();

        $credential = $this->getInstance()->create($userId);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $credential->credentialId->value);
        $this->assertSame($userId, $credential->userId->value);
        $this->assertSame('jwt', $credential->accessToken->jwt->value);
        $this->assertSame('token', $credential->refreshToken->token->value);
        $this->assertSame($now->format('Y-m-d H:i:s'), $credential->refreshToken->expiredAt->value->format('Y-m-d H:i:s'));
        $this->assertTrue($credential->refreshToken->isEnabled->value);
    }

    private function getInstance(): CredentialFactory
    {
        return new CredentialFactory($this->uuid, $this->accessTokenFactory, $this->refreshTokenFactory);
    }
}
