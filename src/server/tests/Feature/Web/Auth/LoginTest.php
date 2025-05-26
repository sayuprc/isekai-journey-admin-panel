<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Auth;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JourneyLog\Route\JourneyLogRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function showLoginForm(): void
    {
        $this->get(route(AuthRouteMap::ShowLoginForm))
            ->assertStatus(200)
            ->assertViewIs('auth.login');
    }

    #[Test]
    public function canLogin(): void
    {
        User::factory()->create([
            'user_id' => Str::uuid()->toString(),
            'email' => 'root@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route(AuthRouteMap::Login), [
            'email' => 'root@example.com',
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertLocation(route(JourneyLogRouteMap::List));

        $this->assertAuthenticated();
    }

    #[Test]
    public function invalidEmail(): void
    {
        User::factory()->create([
            'user_id' => Str::uuid()->toString(),
            'email' => 'root@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route(AuthRouteMap::Login), [
            'email' => 'r@example.com',
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertLocation(route(AuthRouteMap::ShowLoginForm))
            ->assertSessionHasErrors(['message']);
    }

    #[Test]
    public function invalidPassword(): void
    {
        User::factory()->create([
            'user_id' => Str::uuid()->toString(),
            'email' => 'root@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route(AuthRouteMap::Login), [
            'email' => 'root@example.com',
            'password' => 'pass',
        ])
            ->assertStatus(302)
            ->assertLocation(route(AuthRouteMap::ShowLoginForm))
            ->assertSessionHasErrors(['message']);
    }

    #[Test]
    public function notFoundUser(): void
    {
        $this->post(route(AuthRouteMap::Login), [
            'email' => 'root@example.com',
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertLocation(route(AuthRouteMap::ShowLoginForm))
            ->assertSessionHasErrors(['message']);
    }

    #[Test]
    public function invalidTypeEmail(): void
    {
        $this->post(route(AuthRouteMap::Login), [
            'email' => 'aaaa',
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function emptyEmail(): void
    {
        $this->post(route(AuthRouteMap::Login), [
            'email' => '',
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function nullEmail(): void
    {
        $this->post(route(AuthRouteMap::Login), [
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function emptyPassword(): void
    {
        $this->post(route(AuthRouteMap::Login), [
            'email' => 'root@example.com',
            'password' => '',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function nullPassword(): void
    {
        $this->post(route(AuthRouteMap::Login), [
            'email' => 'root@example.com',
            'password' => '',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function afterLogin(): void
    {
        $user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
            'email' => 'root@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->get(route(AuthRouteMap::ShowLoginForm))
            ->assertStatus(302)
            ->assertRedirect(route(JourneyLogRouteMap::List));
    }
}
