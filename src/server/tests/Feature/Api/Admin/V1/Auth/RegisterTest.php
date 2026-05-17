<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use AdminUser\Infrastructures\AdminUserRepository;
use App\Models\AdminUser\AdminUser as ModelsAdminUser;
use App\Models\AdminUser\AdminUserPermission;
use App\Models\AdminUser\RegistrationToken as ModelsRegistrationToken;
use App\Models\Auth\RefreshToken as AuthRefreshToken;
use Auth\Route\AuthRouteMap;
use DateTimeImmutable;
use Illuminate\Testing\Fluent\AssertableJson;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class RegisterTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);
    }

    #[Test]
    public function canRegisterAndConsumeToken(): void
    {
        $tokenId = $this->saveToken('plain-token', 'invitee@example.com', Role::Privilege, ['read_admin_user', 'write_admin_user']);

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->whereAllType([
                    'accessToken' => 'string',
                    'refreshTokenId' => 'string',
                    'refreshToken' => 'string',
                ])->etc(),
            );

        $tokenRow = ModelsRegistrationToken::query()
            ->where('admin_user_registration_token_id', $this->app->make(UuidConverterInterface::class)->toBin($tokenId))
            ->first();
        $this->assertNotNull($tokenRow);
        $this->assertSame(ConsumptionStatus::Consumed->value, $tokenRow->status);

        $userRow = ModelsAdminUser::query()->where('email', 'invitee@example.com')->first();
        $this->assertNotNull($userRow);
        $this->assertSame('新規ユーザー', $userRow->name);
        $this->assertSame(Role::Privilege->value, $userRow->role);

        $permissions = AdminUserPermission::query()
            ->where('admin_user_id', $userRow->admin_user_id)
            ->pluck('permission')
            ->all();
        $this->assertEqualsCanonicalizing(['read_admin_user', 'write_admin_user'], $permissions);

        $refreshTokenRows = AuthRefreshToken::query()->get();
        $this->assertCount(1, $refreshTokenRows);
        $this->assertSame(
            $userRow->admin_user_id,
            $refreshTokenRows->first()->admin_user_id,
        );
    }

    #[Test]
    public function failsWithUnknownToken(): void
    {
        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'wrong-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(400);

        $this->assertSame(0, ModelsAdminUser::query()->count());
        $this->assertSame(ConsumptionStatus::Unused->value, ModelsRegistrationToken::query()->first()->status);
    }

    #[Test]
    public function failsWithUnknownEmail(): void
    {
        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'plain-token',
            'email' => 'no-such@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(400);

        $this->assertSame(0, ModelsAdminUser::query()->count());
        $this->assertSame(ConsumptionStatus::Unused->value, ModelsRegistrationToken::query()->first()->status);
    }

    #[Test]
    public function failsWithExpiredToken(): void
    {
        $this->saveToken('plain-token', 'invitee@example.com', expiredAt: new DateTimeImmutable('-1 second'));

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(400);

        $this->assertSame(0, ModelsAdminUser::query()->count());
        $this->assertSame(ConsumptionStatus::Unused->value, ModelsRegistrationToken::query()->first()->status);
    }

    #[Test]
    public function failsWithAlreadyConsumedToken(): void
    {
        $this->saveToken('plain-token', 'invitee@example.com', status: ConsumptionStatus::Consumed);

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(400);

        $this->assertSame(0, ModelsAdminUser::query()->count());
    }

    #[Test]
    public function failsWithValidationViolation(): void
    {
        $this->saveToken('plain-token', 'invitee@example.com');

        // OpenAPI 仕様で `token` は必須なため、欠落で 422 にする。
        $this->postJson(route(AuthRouteMap::Register), [
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(422);

        $this->assertSame(0, ModelsAdminUser::query()->count());
        $this->assertSame(ConsumptionStatus::Unused->value, ModelsRegistrationToken::query()->first()->status);
    }

    #[Test]
    public function failsWithEmailCollision(): void
    {
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($this->generateUuid(), 'invitee@example.com'),
            HashedPassword::reconstruct('hashed-password'),
        );

        $this->saveToken('plain-token', 'invitee@example.com');

        $this->postJson(route(AuthRouteMap::Register), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
            'password' => 'password',
        ])->assertStatus(400);

        $this->assertSame(1, ModelsAdminUser::query()->count());
        $this->assertSame(ConsumptionStatus::Unused->value, ModelsRegistrationToken::query()->first()->status);
    }

    /**
     * @param list<string> $permissions
     */
    private function saveToken(
        string $plainToken,
        string $email,
        Role $role = Role::General,
        array $permissions = [],
        ?DateTimeImmutable $expiredAt = null,
        ConsumptionStatus $status = ConsumptionStatus::Unused,
    ): string {
        $id = $this->generateUuid();

        // 本番と同じ TokenHasher（bcrypt）でハッシュ化することで、発行と検証経路の整合性を確かめる。
        $hashedToken = $this->app->make(TokenHasherInterface::class)->hash($plainToken);

        $token = new RegistrationToken(
            RegistrationTokenId::reconstruct($id),
            HashedTokenValue::reconstruct($hashedToken),
            Email::reconstruct($email),
            $role,
            Permissions::reconstruct($permissions),
            ExpiredAt::reconstruct($expiredAt ?? new DateTimeImmutable('+7 days')),
            $status,
        );

        $this->app->make(RegistrationTokenRepositoryInterface::class)->save($token);

        return $id;
    }
}
