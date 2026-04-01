<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Song\Route\SongTagRouteMap;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;

class ListSongTagTest extends DatabaseTestCase
{
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $this->withAuth()
            ->get(route(SongTagRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson(['tags' => []]);
    }

    #[Test]
    public function showListWithTags(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $repository = $this->app->make(SongTagRepository::class);
        $repository->save(new SongTag(
            SongTagId::reconstruct($uuid1),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));
        $repository->save(new SongTag(
            SongTagId::reconstruct($uuid2),
            SongTagName::reconstruct('ポップ'),
            OrderNo::reconstruct(20),
        ));

        $this->withAuth()
            ->get(route(SongTagRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'tags' => [
                    ['songTagId' => $uuid1, 'name' => 'ロック', 'orderNo' => 10],
                    ['songTagId' => $uuid2, 'name' => 'ポップ', 'orderNo' => 20],
                ],
            ]);
    }

    #[Test]
    public function returnsUnauthorizedWithoutToken(): void
    {
        $this->get(route(SongTagRouteMap::List))
            ->assertStatus(401);
    }

    #[Test]
    public function returnsForbiddenWithoutPermission(): void
    {
        $this->withAuthAsGeneralUser()
            ->get(route(SongTagRouteMap::List))
            ->assertStatus(403);
    }

    private function withAuthAsGeneralUser(): self
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createAdminUser($this->generateUuid(), 'general@example.com', Role::General);

        $result = $this->app->make(RefreshTokenIssueService::class)->issue($user->adminUserId->value)->unwrap();
        $refreshToken = $result['token'];

        $this->app->make(AdminUserRepository::class)->register($user, HashedPassword::reconstruct('hashed-password'));
        $this->app->make(RefreshTokenRepository::class)->save($refreshToken);

        $accessToken = $this->app->make(AccessTokenIssueService::class)->issue($refreshToken->refreshTokenId->value);

        return $this->withHeader('Authorization', 'Bearer ' . $accessToken->jwt->value);
    }
}
