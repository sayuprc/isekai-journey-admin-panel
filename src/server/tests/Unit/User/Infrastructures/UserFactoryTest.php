<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\UuidGeneratorInterface;
use Tests\TestCase;
use User\Domain\Services\HasherInterface;
use User\Infrastructures\UserFactory;

class UserFactoryTest extends TestCase
{
    private MockInterface&UuidGeneratorInterface $generator;

    private HasherInterface&MockInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
        $this->hasher = Mockery::mock(HasherInterface::class);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->generator->shouldReceive('generate')
            ->andReturn('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $this->hasher->shouldReceive('hash')
            ->with('plain')
            ->andReturn('hashed')
            ->once();

        $user = $this->getInstance()->create('example@example.com', 'plain');

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $user->userId->value);
        $this->assertSame('example@example.com', $user->email->value);
        $this->assertSame('hashed', $user->hashedPassword->value);
    }

    private function getInstance(): UserFactory
    {
        return new UserFactory($this->generator, $this->hasher);
    }
}
