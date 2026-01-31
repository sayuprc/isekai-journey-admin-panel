<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Domain\Models\Email;
use User\Domain\Models\PlainPassword;
use User\Domain\Models\UserId;
use User\Infrastructures\UserFactory;

class UserFactoryTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $user = $this->getInstance()->create(
            UserId::reconstruct('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            Email::reconstruct('example@example.com'),
            PlainPassword::reconstruct('plain'),
        );

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $user->userId->value);
        $this->assertSame('example@example.com', $user->email->value);
        $this->assertNotSame('plain', $user->hashedPassword->value);
    }

    private function getInstance(): UserFactory
    {
        return $this->app->make(UserFactory::class);
    }
}
