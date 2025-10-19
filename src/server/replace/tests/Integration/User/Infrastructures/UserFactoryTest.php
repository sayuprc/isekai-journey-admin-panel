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
        $user = $this->getInstance()->create('example@example.com', 'plain');

        $this->assertSame('example@example.com', $user->email->value);
        $this->assertNotSame('plain', $user->hashedPassword->value);
    }

    private function getInstance(): UserFactory
    {
        return $this->container->get(UserFactory::class);
    }
}
