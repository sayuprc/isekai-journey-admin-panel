<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Credential\RefreshToken;

use Auth\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;

class RefreshTokenFactoryTest extends TestCase
{
    private ClockInterface&MockInterface $clock;

    private MockInterface&UuidGeneratorInterface $uuid;

    private MockInterface&RandomTokenGeneratorInterface $randomToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);
        $this->randomToken = Mockery::mock(RandomTokenGeneratorInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        $userId = $this->generateUuid();

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now = new DateTimeImmutable())
            ->once();

        $this->uuid->shouldReceive('generate')
            ->with()
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->randomToken->shouldReceive('generate')
            ->with()
            ->andReturn('aaaaaaaaaa')
            ->once();

        $result = $this->getInstance()->create($userId);

        $this->assertTrue($result->isOk());

        $refreshToken = $result->unwrap();

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $refreshToken->refreshTokenId->value);
        $this->assertSame($userId, $refreshToken->userId->value);
        $this->assertSame('aaaaaaaaaa', $refreshToken->token->value);
        $expiredAtProp = new ReflectionProperty($refreshToken, 'expiredAt');
        $expiredAtProp->setAccessible(true);
        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $expiredAtProp->getValue($refreshToken)->value->format('Y-m-d H:i:s'));
        $this->assertTrue($refreshToken->isAvailable($now));
    }

    private function getInstance(): RefreshTokenFactory
    {
        return new RefreshTokenFactory($this->clock, $this->uuid, $this->randomToken);
    }
}
