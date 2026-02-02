<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Domain\Models\Email;
use User\Domain\Models\PlainPassword;
use User\Domain\Models\UserId;
use User\Domain\Services\HasherInterface;
use User\Infrastructures\UserFactory;

class UserFactoryTest extends TestCase
{
    private HasherInterface&MockInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = Mockery::mock(HasherInterface::class);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->hasher->shouldReceive('hash')
            ->with('plain')
            ->andReturn('hashed')
            ->once();

        $user = $this->getInstance()->create(
            UserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            Email::reconstruct('example@example.com'),
            PlainPassword::reconstruct('plain'),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $user->userId->value);
        $this->assertSame('example@example.com', $user->email->value);
        $this->assertSame('hashed', $user->hashedPassword->value);
    }

    private function getInstance(): UserFactory
    {
        return new UserFactory($this->hasher);
    }
}
