<?php

declare(strict_types=1);

namespace Tests\Integration\User\Application\Interactors;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\Application\Interactors\AuthenticateInteractor;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\DebugInfrastructures\FileRefreshTokenRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Infrastructures\Credential\AccessToken\AccessTokenFactory;
use User\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
use User\Infrastructures\UserFactory;

class AuthenticateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canAuthenticate(): void
    {
        config()->set('auth.jwt.key', 'key');

        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        config()->set('auth.jwt.key', 'key');

        $now = now()->toImmutable();
        CarbonImmutable::setTestNow($now->modify('-3 hours'));

        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        CarbonImmutable::setTestNow($now);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        config()->set('auth.jwt.key', 'key');

        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        config()->set('auth.jwt.key', 'key');

        $user = $this->app->make(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): AuthenticateInteractor
    {
        return $this->app->make(AuthenticateInteractor::class);
    }
}
