<?php

declare(strict_types=1);

namespace Tests\Integration\User\Application\Interactors;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\Application\Interactors\LoginInteractor;
use User\Application\UseCase\Login\LoginInputData;
use User\DebugInfrastructures\FileRefreshTokenRepository;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Credential\RefreshToken\RefreshToken;
use User\Infrastructures\UserFactory;

class LoginInteractorTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canLogin(): void
    {
        Chronos::setTestNow('2019-12-02 12:34:29');

        $now = new Chronos();

        $email = 'user@example.com';
        $password = 'plain password';

        $user = $this->container->get(UserFactory::class)->create($email, $password);
        $this->factory(FileUserRepository::class, $user->userId->value, $user);

        $result = $this->getInstance()->handle(new LoginInputData($email, $password));

        $this->assertTrue($result->isOk());

        /** @var array<RefreshToken> */
        $refreshTokens = $this->getAll(FileRefreshTokenRepository::class);
        $this->assertCount(1, $refreshTokens);
        $this->assertTrue($refreshTokens[array_key_first($refreshTokens)]->isEnabled($now));
    }

    private function getInstance(): LoginInteractor
    {
        return $this->container->get(LoginInteractor::class);
    }
}
