<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential;

use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Tests\TestCase;
use User\Domain\Services\RandomTokenGeneratorInterface;
use User\Infrastructures\Credential\RefreshTokenFactory;

class RefreshTokenFactoryTest extends TestCase
{
    private ClockInterface&MockInterface $clock;

    private MockInterface&RandomTokenGeneratorInterface $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->generator = Mockery::mock(RandomTokenGeneratorInterface::class);
    }

    #[Test]
    public function createSuccessfully(): void
    {
        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now = new DateTimeImmutable())
            ->once();

        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn('aaaaaaaaaa')
            ->once();

        $refreshToken = $this->getInstance()->create();

        $this->assertSame('aaaaaaaaaa', $refreshToken->token->value);
        $this->assertSame($now->modify('+7 days')->format('Y-m-d H:i:s'), $refreshToken->expiredAt->value->format('Y-m-d H:i:s'));
        $this->assertTrue($refreshToken->isEnabled->value);
    }

    private function getInstance(): RefreshTokenFactory
    {
        return new RefreshTokenFactory($this->clock, $this->generator);
    }
}
