<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Infrastructures\Credential\CredentialFactory;

class CredentialFactoryTest extends TestCase
{
    #[Test]
    public function createSuccessfully(): void
    {
        $userId = $this->generateUuid();

        $credential = $this->getInstance()->create($userId);

        $this->assertSame($userId, $credential->userId->value);
    }

    private function getInstance(): CredentialFactory
    {
        return $this->app->make(CredentialFactory::class);
    }
}
