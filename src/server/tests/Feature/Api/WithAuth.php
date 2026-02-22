<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\Role;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;

trait WithAuth
{
    use EntityFactory;
    use FileRepositoryTransaction;

    private function withAuth(): self
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createAdminUser($this->generateUuid(), 'root@example.com', Role::Privilege);

        $refreshToken = $this->app->make(RefreshTokenIssueService::class)->issue($user->adminUserId->value)->unwrap();

        $this->factory(FileAdminUserRepository::class, $user->toArray());
        $this->factory(FileRefreshTokenRepository::class, $refreshToken->toArray());

        $accessToken = $this->app->make(AccessTokenIssueService::class)->issue($refreshToken->refreshTokenId->value);

        return $this->withUnencryptedCookie('access_token', $accessToken->jwt->value)
            ->withCredentials();
    }
}
