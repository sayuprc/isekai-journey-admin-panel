<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use Auth\Route\AuthRouteMap;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use EntityFactory;
    use FileRepositoryTransaction;

    #[Test]
    public function canLogin(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $hashedPassword = Hash::make('password');
        $user = $this->createUser($this->generateUuid(), 'example@example.com');

        $this->factory(FileAdminUserRepository::class, [...$user->toArray(), 'hashed_password' => $hashedPassword]);

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'example@example.com',
            'password' => 'password',
        ])->assertStatus(200)
            ->assertCookie('access_token')
            ->assertCookie('refresh_token');
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
        $user = $this->createUser($this->generateUuid(), 'example@example.com');

        $this->factory(FileAdminUserRepository::class, [...$user->toArray(), 'hashed_password' => $hashedPassword]);

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
