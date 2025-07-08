<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;
use User\Infrastructures\Credential\RefreshTokenFactory;

class RefreshTokenFactoryTest extends TestCase
{
    private ClockInterface&MockInterface $clock;

    private MockInterface&UuidGeneratorInterface $uuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->uuid = Mockery::mock(UuidGeneratorInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now = new DateTimeImmutable())
            ->once();

        $this->uuid->shouldReceive('generate')
            ->with()
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $refreshToken = $this->getInstance()->create();

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $refreshToken->token->value);
        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $refreshToken->expiredAt->value->format('Y-m-d H:i:s'));
        $this->assertTrue($refreshToken->isEnabled->value);
    }

    private function getInstance(): RefreshTokenFactory
    {
        return new RefreshTokenFactory($this->clock, $this->uuid);
    }
}
