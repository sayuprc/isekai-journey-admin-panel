<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Infrastructures\AdminUserRepository;
use App\Models\Auth\RefreshToken as AuthRefreshToken;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyVerificationResult;
use Auth\Route\AuthRouteMap;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Testing\Fluent\AssertableJson;
use Override;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class LoginTest extends DatabaseTestCase
{
    use AssertsAuditLog;
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
    public function canStartLogin(): void
    {
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');

        $this->postJson(route(AuthRouteMap::LoginStart), [
            'email' => 'example@example.com',
        ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->whereType('authCeremonyId', 'string')
                    ->where('publicKey.challenge', 'login-challenge')
                    ->where('publicKey.allowCredentials.0.id', 'credential-id')
                    ->etc(),
            );

        $this->assertSame($adminUserId, $this->findPasskey('credential-id')->adminUserId);
        $this->assertSame(0, AuthRefreshToken::query()->count());
    }

    #[Test]
    public function startFailsWithUnknownEmail(): void
    {
        $this->bindPasskeyAuthenticator();

        $this->postJson(route(AuthRouteMap::LoginStart), [
            'email' => 'unknown@example.com',
        ])->assertStatus(401);

        $this->assertSame(0, AuthRefreshToken::query()->count());
    }

    #[Test]
    public function startFailsWhenUserHasNoPasskey(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($this->generateUuid(), 'example@example.com'),
        );

        $this->postJson(route(AuthRouteMap::LoginStart), [
            'email' => 'example@example.com',
        ])->assertStatus(401);

        $this->assertSame(0, AuthRefreshToken::query()->count());
    }

    #[Test]
    public function canFinishLoginAndUpdatesPasskeyAndAuditLog(): void
    {
        CarbonImmutable::setTestNow('2026-01-02 03:04:05');
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');

        $authCeremonyId = $this->startLogin('example@example.com');

        $this->postJson(route(AuthRouteMap::LoginFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) => $json->whereAllType([
                    'accessToken' => 'string',
                    'refreshTokenId' => 'string',
                    'refreshToken' => 'string',
                ])->etc(),
            );

        $passkey = $this->findPasskey('credential-id');
        $this->assertSame(456, $passkey->signCount);
        $this->assertSame('2026-01-02 03:04:05', $passkey->lastUsedAt?->format('Y-m-d H:i:s'));
        $this->assertSame(1, AuthRefreshToken::query()->count());

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Login, AuditTargetType::AdminUser, $adminUserId);
        $this->assertSame($adminUserId, $log['admin_user_id']);
        $snapshot = $log['snapshot'];
        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('refresh_token_id', $snapshot);
        $this->assertArrayHasKey('admin_user_passkey_id', $snapshot);
    }

    #[Test]
    public function finishFailsWithMissingCeremonyWithoutDbChanges(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->storeAdminUserWithPasskey('example@example.com');

        $this->postJson(route(AuthRouteMap::LoginFinish), [
            'authCeremonyId' => $this->generateUuid(),
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(401);

        $this->assertPasskeyUnchanged('credential-id');
        $this->assertSame(0, AuthRefreshToken::query()->count());
        $this->assertAuditLogCount(0);
    }

    #[Test]
    public function finishFailsWithWrongCeremonyTypeWithoutDbChanges(): void
    {
        $this->bindPasskeyAuthenticator();
        $adminUserId = $this->storeAdminUserWithPasskey('example@example.com');
        $authCeremonyId = $this->generateUuid();

        $this->app->make(PasskeyCeremonyStoreInterface::class)->put(new PasskeyCeremonyState(
            $authCeremonyId,
            'register',
            'plain-token',
            'example@example.com',
            'テストユーザー',
            $adminUserId,
            '{"challenge":"register-challenge"}',
        ));

        $this->postJson(route(AuthRouteMap::LoginFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(401);

        $this->assertPasskeyUnchanged('credential-id');
        $this->assertSame(0, AuthRefreshToken::query()->count());
        $this->assertAuditLogCount(0);
    }

    #[Test]
    public function finishFailsWithCredentialIdForDifferentUserWithoutDbChanges(): void
    {
        $this->bindPasskeyAuthenticator();
        $this->storeAdminUserWithPasskey('example@example.com', 'credential-id');
        $this->storeAdminUserWithPasskey('other@example.com', 'other-credential-id');

        $authCeremonyId = $this->startLogin('example@example.com');

        $this->postJson(route(AuthRouteMap::LoginFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'other-credential-id'],
        ])->assertStatus(401);

        $this->assertPasskeyUnchanged('credential-id');
        $this->assertPasskeyUnchanged('other-credential-id');
        $this->assertSame(0, AuthRefreshToken::query()->count());
        $this->assertAuditLogCount(0);
    }

    #[Test]
    public function finishFailsWithoutDbChangesWhenPasskeyVerificationFails(): void
    {
        $this->bindPasskeyAuthenticator(failFinish: true);
        $this->storeAdminUserWithPasskey('example@example.com');

        $authCeremonyId = $this->startLogin('example@example.com');

        $this->postJson(route(AuthRouteMap::LoginFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(401);

        $this->assertPasskeyUnchanged('credential-id');
        $this->assertSame(0, AuthRefreshToken::query()->count());
        $this->assertAuditLogCount(0);
    }

    private function startLogin(string $email): string
    {
        $response = $this->postJson(route(AuthRouteMap::LoginStart), [
            'email' => $email,
        ])->assertStatus(200);

        $authCeremonyId = $response->json('authCeremonyId');
        $this->assertIsString($authCeremonyId);

        return $authCeremonyId;
    }

    private function storeAdminUserWithPasskey(string $email, string $credentialId = 'credential-id'): string
    {
        $adminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, $email),
        );

        $this->app->make(AdminUserPasskeyRepositoryInterface::class)->save(new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            $credentialId,
            'public-key',
            123,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        ));

        return $adminUserId;
    }

    private function findPasskey(string $credentialId): AdminUserPasskey
    {
        $passkey = $this->app->make(AdminUserPasskeyRepositoryInterface::class)->findByCredentialId($credentialId);

        $this->assertNotNull($passkey);

        return $passkey;
    }

    private function assertPasskeyUnchanged(string $credentialId): void
    {
        $passkey = $this->findPasskey($credentialId);
        $this->assertSame(123, $passkey->signCount);
        $this->assertNull($passkey->lastUsedAt);
    }

    private function bindPasskeyAuthenticator(bool $failFinish = false): void
    {
        $this->app->bind(PasskeyAuthenticatorInterface::class, fn (): PasskeyAuthenticatorInterface => new class ($failFinish) implements PasskeyAuthenticatorInterface {
            public function __construct(private readonly bool $failFinish)
            {
            }

            public function startRegistration(string $userHandle, string $userName, string $displayName): PasskeyStartResult
            {
                throw new RuntimeException('unused');
            }

            public function finishRegistration(array $credential, string $optionsJson): PasskeyVerificationResult
            {
                throw new RuntimeException('unused');
            }

            public function startAuthentication(array $passkeys): PasskeyStartResult
            {
                /** @var list<AdminUserPasskey> $passkeys */
                return new PasskeyStartResult('{"challenge":"login-challenge"}', [
                    'challenge' => 'login-challenge',
                    'allowCredentials' => array_map(
                        fn (AdminUserPasskey $passkey): array => ['type' => 'public-key', 'id' => $passkey->credentialId],
                        $passkeys,
                    ),
                ]);
            }

            public function finishAuthentication(array $credential, string $optionsJson, AdminUserPasskey $passkey, string $userHandle): PasskeyVerificationResult
            {
                if ($this->failFinish) {
                    throw new RuntimeException('verification failed');
                }

                return new PasskeyVerificationResult($passkey->credentialId, $passkey->publicKey, 456);
            }
        });
    }
}
