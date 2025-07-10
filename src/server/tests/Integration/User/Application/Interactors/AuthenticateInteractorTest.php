<?php

declare(strict_types=1);

namespace Tests\Integration\User\Application\Interactors;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\Application\Interactors\AuthenticateInteractor;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\DebugInfrastructures\FileCredentialRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Infrastructures\Credential\CredentialFactory;
use User\Infrastructures\UserFactory;

class AuthenticateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canAuthenticate(): void
    {
        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $credential = $this->app->make(CredentialFactory::class)->create($user->userId->value);

        $this->factory(FileCredentialRepository::class, $credential->credentialId->value, $credential);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($credential->accessToken->jwt->value));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        $now = now()->toImmutable();
        CarbonImmutable::setTestNow($now->modify('-3 hours'));

        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $credential = $this->app->make(CredentialFactory::class)->create($user->userId->value);

        CarbonImmutable::setTestNow($now);

        $this->factory(FileCredentialRepository::class, $credential->credentialId->value, $credential);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($credential->accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $credential = $this->app->make(CredentialFactory::class)->create($user->userId->value);

        $result = $this->getInstance()->handle(new AuthenticateInputData($credential->accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $credential = $this->app->make(CredentialFactory::class)->create($user->userId->value);

        $this->factory(FileCredentialRepository::class, $credential->credentialId->value, $credential);

        $result = $this->getInstance()->handle(new AuthenticateInputData($credential->accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): AuthenticateInteractor
    {
        return $this->app->make(AuthenticateInteractor::class);
    }
}
