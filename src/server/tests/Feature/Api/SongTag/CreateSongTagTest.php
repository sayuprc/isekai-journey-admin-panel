<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongTag;

use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Tag\SongTag as SongTagDomain;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Infrastructures\SongTagRepository;
use Song\Route\SongTagRouteMap;
use Support\Domain\ValueObjects\OrderNo;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class CreateSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canCreate(): void
    {
        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => 'ロック',
            ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has(
                        'tag',
                        fn (AssertableJson $json) => $json
                            ->whereType('songTagId', 'string')
                            ->where('name', 'ロック')
                            ->where('orderNo', 10),
                    ),
            );
    }

    #[Test]
    public function returnsUnauthorizedWithoutToken(): void
    {
        $this->postJson(route(SongTagRouteMap::Create), [
            'name' => 'ロック',
        ])->assertStatus(401);
    }

    #[Test]
    public function returnsForbiddenWithoutPermission(): void
    {
        $this->withAuthAsGeneralUser()
            ->postJson(route(SongTagRouteMap::Create), [
                'name' => 'ロック',
            ])->assertStatus(403);
    }

    #[Test]
    public function returnsErrorWhenDuplicateName(): void
    {
        $this->app->make(SongTagRepository::class)->save(new SongTagDomain(
            SongTagId::reconstruct($this->generateUuid()),
            SongTagName::reconstruct('ロック'),
            OrderNo::reconstruct(10),
        ));

        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), ['name' => 'ロック'])
            ->assertStatus(400)
            ->assertJson(['message' => 'すでに使われているタグ名です "ロック"']);
    }

    #[Test]
    public function returnsValidationErrorWhenNameIsEmpty(): void
    {
        $this->withAuth()
            ->postJson(route(SongTagRouteMap::Create), ['name' => ''])
            ->assertStatus(422);
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
