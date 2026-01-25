<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use Auth\Route\AuthRouteMap;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FileRepositoryTransaction;
use Tests\TestCase;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserId;

class LoginTest extends TestCase
{
    use FileRepositoryTransaction;

    #[Test]
    public function canLogin(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = new User(
            new UserId($this->generateUuid()),
            new Email('example@example.com'),
            new HashedPassword(Hash::make('password')),
        );

        $this->factory(FileUserRepository::class, $user->userId->value, $user);

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
        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'example@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    }

    #[Test]
    public function invalidCredentials(): void
    {
        $user = new User(
            new UserId($this->generateUuid()),
            new Email('example@example.com'),
            new HashedPassword(Hash::make('password')),
        );

        $this->factory(FileUserRepository::class, $user->userId->value, $user);

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
