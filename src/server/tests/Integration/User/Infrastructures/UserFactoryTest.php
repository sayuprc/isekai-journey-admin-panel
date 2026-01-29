<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Infrastructures\UserFactory;

class UserFactoryTest extends TestCase
{
    #[Test]
    public function canCreate(): void
    {
        $userResult = $this->getInstance()->create('example@example.com', 'plain');

        $this->assertTrue($userResult->isOk());

        $user = $userResult->unwrap();

        $this->assertSame('example@example.com', $user->email->value);
        $this->assertNotSame('plain', $user->hashedPassword->value);
    }

    private function getInstance(): UserFactory
    {
        return $this->app->make(UserFactory::class);
    }
}
