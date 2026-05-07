<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Route\AuthRouteMap;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class LoginTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canLogin(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $hashedPassword = Hash::make('password');
        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com');

        $this->app->make(AdminUserRepository::class)->register($user, HashedPassword::reconstruct($hashedPassword));

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'example@example.com',
            'password' => 'password',
        ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->has('accessToken')
                    ->has('refreshTokenId')
                    ->has('refreshToken')
                    ->whereAllType([
                        'accessToken' => 'string',
                        'refreshTokenId' => 'string',
                        'refreshToken' => 'string',
                    ])
                    ->etc(),
            );
    }

    #[Test]
    public function userNotFound(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'example@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    }

    #[Test]
    public function invalidCredentials(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $hashedPassword = Hash::make('password');
        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com');

        $this->app->make(AdminUserRepository::class)->register($user, HashedPassword::reconstruct($hashedPassword));

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'example@example.com',
            'password' => 'invalid-password',
        ])->assertStatus(401);

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'invalid@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    }
}
