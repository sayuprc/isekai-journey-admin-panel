<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\Interactors;

use Auth\Application\Interactors\AuthenticateInteractor;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Infrastructures\Credential\AccessToken\AccessTokenFactory;
use Auth\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\DebugInfrastructures\FileUserRepository;

class AuthenticateInteractorTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canAuthenticate(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value)->unwrap();
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value)->unwrap();

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $now = now()->toImmutable();
        CarbonImmutable::setTestNow($now->modify('-3 hours'));

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value)->unwrap();
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value)->unwrap();

        CarbonImmutable::setTestNow($now);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value)->unwrap();
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value)->unwrap();

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createUser($this->generateUuid(), 'example@example.com', 'password');
        $refreshToken = $this->app->make(RefreshTokenFactory::class)->create($user->userId->value)->unwrap();
        $accessToken = $this->app->make(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value)->unwrap();

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): AuthenticateInteractor
    {
        return $this->app->make(AuthenticateInteractor::class);
    }
}
