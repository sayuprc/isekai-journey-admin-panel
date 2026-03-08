<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Tests\Support\Domain\EntityFactory;

trait WithAuth
{
    use EntityFactory;

    private function withAuth(): self
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createAdminUser($this->generateUuid(), 'root@example.com', Role::Privilege);

        $refreshToken = $this->app->make(RefreshTokenIssueService::class)->issue($user->adminUserId->value)->unwrap();

        $this->app->make(AdminUserRepository::class)->register($user, HashedPassword::reconstruct('hashed-password'));
        $this->app->make(RefreshTokenRepository::class)->save($refreshToken);

        $accessToken = $this->app->make(AccessTokenIssueService::class)->issue($refreshToken->refreshTokenId->value);

        return $this->withHeader('Authorization', 'Bearer ' . $accessToken->jwt->value);
    }
}
