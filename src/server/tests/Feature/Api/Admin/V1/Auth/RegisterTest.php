<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use App\Models\Auth\AdminRegistrationToken as AuthAdminRegistrationToken;
use App\Models\AdminUser\AdminUser as AuthAdminUser;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationTokenRepositoryInterface;
use Auth\Domain\Models\AdminRegistrationToken\ConsumptionStatus;
use Auth\Route\AuthRouteMap;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RegisterTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canRegisterWithValidRegistrationToken(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $plainToken = 'registration-token';
        $token = $this->createRegistrationToken(
            'example@example.com',
            Role::General,
            $this->hashToken($plainToken),
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );
        $this->tokenRepository()->save($token);

        $this->postJson(route(AuthRouteMap::Register), [
            'name' => '登録ユーザー',
            'email' => 'example@example.com',
            'password' => 'plain-password',
            'registrationToken' => $plainToken,
        ])->assertStatus(204);

        $this->assertSame(1, AuthAdminUser::query()->count());
        $this->assertSame('example@example.com', AuthAdminUser::query()->firstOrFail()->email);
        $this->assertSame(Role::General->value, AuthAdminUser::query()->firstOrFail()->role);

        $stored = AuthAdminRegistrationToken::query()
            ->where('admin_registration_token_id', $this->uuidConverter()->toBin($token->adminRegistrationTokenId->value))
            ->firstOrFail();

        $this->assertSame(ConsumptionStatus::Consumed->value, $stored->status);
        $this->assertSame([], $this->tokenRepository()->findAvailableByEmail(Email::reconstruct('example@example.com')));
    }

    #[Test]
    public function registrationFailsWhenTokenIsExpired(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $this->tokenRepository()->save($this->createRegistrationToken(
            'example@example.com',
            Role::General,
            $this->hashToken('expired-token'),
            now()->subMinute()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        ));

        $this->postJson(route(AuthRouteMap::Register), [
            'name' => '登録ユーザー',
            'email' => 'example@example.com',
            'password' => 'plain-password',
            'registrationToken' => 'expired-token',
        ])->assertStatus(400)
            ->assertJsonPath('message', '登録トークンが無効です');
    }

    #[Test]
    public function registrationFailsWhenTokenIsConsumed(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $this->tokenRepository()->save($this->createRegistrationToken(
            'example@example.com',
            Role::General,
            $this->hashToken('consumed-token'),
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Consumed,
        ));

        $this->postJson(route(AuthRouteMap::Register), [
            'name' => '登録ユーザー',
            'email' => 'example@example.com',
            'password' => 'plain-password',
            'registrationToken' => 'consumed-token',
        ])->assertStatus(400)
            ->assertJsonPath('message', '登録トークンが無効です');
    }

    #[Test]
    public function registrationFailsWhenEmailDoesNotMatchToken(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $this->tokenRepository()->save($this->createRegistrationToken(
            'allowed@example.com',
            Role::General,
            $this->hashToken('allowed-token'),
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        ));

        $this->postJson(route(AuthRouteMap::Register), [
            'name' => '登録ユーザー',
            'email' => 'other@example.com',
            'password' => 'plain-password',
            'registrationToken' => 'allowed-token',
        ])->assertStatus(400)
            ->assertJsonPath('message', '登録トークンが無効です');
    }

    #[Test]
    public function registrationFailsWhenEmailAlreadyExists(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $existingUser = $this->createAdminUser($this->generateUuid(), 'example@example.com');
        $this->app->make(AdminUserRepository::class)->register($existingUser, HashedPassword::reconstruct('hashed-password'));

        $this->tokenRepository()->save($this->createRegistrationToken(
            'example@example.com',
            Role::General,
            $this->hashToken('duplicate-token'),
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        ));

        $this->postJson(route(AuthRouteMap::Register), [
            'name' => '登録ユーザー',
            'email' => 'example@example.com',
            'password' => 'plain-password',
            'registrationToken' => 'duplicate-token',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'すでに使われているメールアドレスです "example@example.com"');
    }

    #[Test]
    public function canRegisterPrivilegeUserWhenRegistrationTokenIsPrivilege(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');

        $plainToken = 'privilege-registration-token';
        $this->tokenRepository()->save($this->createRegistrationToken(
            'privilege@example.com',
            Role::Privilege,
            $this->hashToken($plainToken),
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        ));

        $this->postJson(route(AuthRouteMap::Register), [
            'name' => '特権ユーザー',
            'email' => 'privilege@example.com',
            'password' => 'plain-password',
            'registrationToken' => $plainToken,
        ])->assertStatus(204);

        $user = AuthAdminUser::query()->sole();
        $this->assertSame(Role::Privilege->value, $user->role);
    }

    private function tokenRepository(): AdminRegistrationTokenRepositoryInterface
    {
        return $this->app->make(AdminRegistrationTokenRepositoryInterface::class);
    }

    private function uuidConverter(): UuidConverterInterface
    {
        return $this->app->make(UuidConverterInterface::class);
    }

    private function hashToken(string $plainToken): string
    {
        return $this->app->make(\Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface::class)->hash($plainToken);
    }

    private function createRegistrationToken(
        string $email,
        Role $role,
        string $token,
        \DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): AdminRegistrationToken {
        return AdminRegistrationToken::reconstruct(
            $this->generateUuid(),
            $email,
            $role->value,
            $token,
            $expiredAt,
            $status->value,
        );
    }
}
