<?php

declare(strict_types=1);

namespace Tests\Integration\User\Application\Interactors;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\Application\Interactors\LoginInteractor;
use User\Application\UseCase\Login\LoginInputData;
use User\DebugInfrastructures\FileCredentialRepository;
use User\Domain\Models\Credential\Credential;

class LoginInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canLogin(): void
    {
        $userId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->getInstance()->handle(new LoginInputData($userId));

        /** @var array<Credential> $credentials */
        $credentials = $this->getAll(FileCredentialRepository::class);
        $this->assertCount(1, $credentials);
        $this->assertSame($userId, $credentials[array_key_first($credentials)]->userId->value);
        $this->assertTrue($credentials[array_key_first($credentials)]->isEnabled());
    }

    private function getInstance(): LoginInteractor
    {
        return $this->app->make(LoginInteractor::class);
    }
}
