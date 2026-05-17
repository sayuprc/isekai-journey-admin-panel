<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\Invitation\ConsumedAt;
use AdminUser\Domain\Models\Invitation\ExpiresAt;
use AdminUser\Domain\Models\Invitation\Invitation;
use AdminUser\Domain\Models\Invitation\InvitationId;
use AdminUser\Domain\Models\Invitation\PlainToken;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\TokenHasherInterface;
use AdminUser\Infrastructures\InvitationRepository;
use App\Models\AdminUser\AdminUser as ModelsAdminUser;
use App\Models\AdminUser\AdminUserInvitation;
use Auth\Route\AuthRouteMap;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class RegisterTest extends DatabaseTestCase
{
    private function setupJwtConfig(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);
    }

    private function saveInvitation(
        string $plainToken,
        Role $role = Role::General,
        array $permissions = [],
        ?DateTimeImmutable $expiresAt = null,
        ?DateTimeImmutable $consumedAt = null,
    ): Invitation {
        $hashed = $this->app->make(TokenHasherInterface::class)->hash(PlainToken::reconstruct($plainToken));

        $invitation = new Invitation(
            InvitationId::reconstruct($this->generateUuid()),
            $hashed,
            $role,
            Permissions::reconstruct($permissions),
            ExpiresAt::reconstruct($expiresAt ?? new DateTimeImmutable('+1 day')),
            is_null($consumedAt) ? null : ConsumedAt::reconstruct($consumedAt),
        );

        $this->app->make(InvitationRepository::class)->save($invitation);

        return $invitation;
    }

    #[Test]
    public function canRegisterWithValidToken(): void
    {
        $this->setupJwtConfig();

        $token = str_repeat('a', 64);
        $this->saveInvitation(
            $token,
            Role::Privilege,
            ['read_admin_user'],
        );

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => $token,
            'name' => 'まいく',
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(200);

        $this->assertTrue(
            ModelsAdminUser::query()->where('email', 'invitee@example.com')->exists(),
        );

        $invitation = AdminUserInvitation::query()->firstOrFail();
        $this->assertNotNull($invitation->consumed_at);
    }

    #[Test]
    public function canLoginAfterRegister(): void
    {
        $this->setupJwtConfig();

        $token = str_repeat('b', 64);
        $this->saveInvitation($token);

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => $token,
            'name' => 'まいく',
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(200);

        $this->postJson(route(AuthRouteMap::Login), [
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(200);
    }

    #[Test]
    public function unknownTokenReturns401(): void
    {
        $this->setupJwtConfig();

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => str_repeat('c', 64),
            'name' => 'まいく',
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(401);
    }

    #[Test]
    public function malformedTokenReturns401(): void
    {
        $this->setupJwtConfig();

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'too-short',
            'name' => 'まいく',
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(401);
    }

    #[Test]
    public function expiredTokenReturns401(): void
    {
        $this->setupJwtConfig();

        $token = str_repeat('d', 64);
        $this->saveInvitation(
            $token,
            expiresAt: new DateTimeImmutable('-1 day'),
        );

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => $token,
            'name' => 'まいく',
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(401);
    }

    #[Test]
    public function alreadyConsumedTokenReturns401(): void
    {
        $this->setupJwtConfig();

        $token = str_repeat('e', 64);
        $this->saveInvitation(
            $token,
            consumedAt: new DateTimeImmutable('-1 hour'),
        );

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => $token,
            'name' => 'まいく',
            'email' => 'invitee@example.com',
            'password' => 'plain-password',
        ])->assertStatus(401);
    }

    #[Test]
    public function invalidInputReturns422(): void
    {
        $this->setupJwtConfig();

        $token = str_repeat('f', 64);
        $this->saveInvitation($token);

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => $token,
            'name' => '',
            'email' => 'not-an-email',
            'password' => '',
        ])->assertStatus(422);
    }
}
