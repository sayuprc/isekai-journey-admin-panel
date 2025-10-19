<?php

declare(strict_types=1);

namespace Tests\Integration\User\Application\Interactors;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\Application\Interactors\AuthenticateInteractor;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\DebugInfrastructures\FileRefreshTokenRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Services\Credential\AccessToken\JwtConfig;
use User\Infrastructures\Credential\AccessToken\AccessTokenFactory;
use User\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;
use User\Infrastructures\UserFactory;

class AuthenticateInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canAuthenticate(): void
    {
        $this->setConfig();

        $user = $this->container->get(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->container->get(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->container->get(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        $this->setConfig();

        $now = new Chronos();
        Chronos::setTestNow($now->modify('-3 hours'));

        $user = $this->container->get(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->container->get(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->container->get(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        Chronos::setTestNow($now);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        $this->setConfig();

        $user = $this->container->get(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->container->get(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->container->get(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        $this->setConfig();

        $user = $this->container->get(UserFactory::class)->create('example@example.com', 'password');
        $refreshToken = $this->container->get(RefreshTokenFactory::class)->create($user->userId->value);
        $accessToken = $this->container->get(AccessTokenFactory::class)->create($refreshToken->refreshTokenId->value);

        $this->factory(FileRefreshTokenRepository::class, $refreshToken->refreshTokenId->value, $refreshToken);

        $result = $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertTrue($result->isErr());
    }

    private function setConfig(): void
    {
        $this->container->register(JwtConfig::class, fn () => new JwtConfig(iss: 'iss', alg: 'HS256', key: 'key'));
    }

    private function getInstance(): AuthenticateInteractor
    {
        return $this->container->get(AuthenticateInteractor::class);
    }
}
