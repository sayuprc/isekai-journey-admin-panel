<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Route\AuthRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class LoginTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function passwordLoginFailsEvenWhenUserExists(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com');

        $this->app->make(AdminUserRepository::class)->register($user);

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'example@example.com',
            'password' => 'password',
        ])->assertStatus(401);
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

        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com');

        $this->app->make(AdminUserRepository::class)->register($user);

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
