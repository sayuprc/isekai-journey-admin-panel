<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Auth;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use App\Models\AdminUser\AdminUser as ModelsAdminUser;
use App\Models\AdminUser\AdminUserPasskey as ModelsAdminUserPasskey;
use App\Models\AdminUser\RegistrationToken as ModelsRegistrationToken;
use App\Models\Auth\RefreshToken as AuthRefreshToken;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyVerificationResult;
use Auth\Route\AuthRouteMap;
use DateTimeImmutable;
use Illuminate\Testing\Fluent\AssertableJson;
use Override;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;

class RegisterFinishTest extends DatabaseTestCase
{
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
    public function canFinishRegistrationAndConsumeToken(): void
    {
        $this->bindPasskeyAuthenticator(false);
        $tokenId = $this->saveToken('plain-token', 'invitee@example.com');

        $authCeremonyId = $this->startRegistration();

        $this->postJson(route(AuthRouteMap::RegisterFinish), [
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

        $tokenRow = ModelsRegistrationToken::query()
            ->where('admin_user_registration_token_id', $this->app->make(UuidConverterInterface::class)->toBin($tokenId))
            ->first();
        $this->assertNotNull($tokenRow);
        $this->assertSame(ConsumptionStatus::Consumed->value, $tokenRow->status);

        $userRow = ModelsAdminUser::query()->where('email', 'invitee@example.com')->first();
        $this->assertNotNull($userRow);
        $this->assertSame('新規ユーザー', $userRow->name);
        $this->assertArrayNotHasKey('password', $userRow->getAttributes());

        $this->assertSame(1, ModelsAdminUserPasskey::query()->count());
        $this->assertSame(1, AuthRefreshToken::query()->count());
    }

    #[Test]
    public function failsWithoutDbChangesWhenPasskeyVerificationFails(): void
    {
        $this->bindPasskeyAuthenticator(true);
        $this->saveToken('plain-token', 'invitee@example.com');

        $authCeremonyId = $this->startRegistration();

        $this->postJson(route(AuthRouteMap::RegisterFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'credential-id'],
        ])->assertStatus(400);

        $this->assertSame(0, ModelsAdminUser::query()->count());
        $this->assertSame(0, ModelsAdminUserPasskey::query()->count());
        $this->assertSame(0, AuthRefreshToken::query()->count());
        $token = ModelsRegistrationToken::query()->first();
        $this->assertNotNull($token);
        $this->assertSame(ConsumptionStatus::Unused->value, $token->status);
    }

    private function startRegistration(): string
    {
        $response = $this->postJson(route(AuthRouteMap::RegisterStart), [
            'token' => 'plain-token',
            'email' => 'invitee@example.com',
            'name' => '新規ユーザー',
        ])->assertStatus(200);

        $authCeremonyId = $response->json('authCeremonyId');
        $this->assertIsString($authCeremonyId);

        return $authCeremonyId;
    }

    private function bindPasskeyAuthenticator(bool $failFinish): void
    {
        $this->app->bind(PasskeyAuthenticatorInterface::class, fn (): PasskeyAuthenticatorInterface => new class ($failFinish) implements PasskeyAuthenticatorInterface {
            public function __construct(private readonly bool $failFinish)
            {
            }

            public function startRegistration(string $userHandle, string $userName, string $displayName): PasskeyStartResult
            {
                return new PasskeyStartResult('{"challenge":"challenge"}', ['challenge' => 'challenge']);
            }

            public function finishRegistration(array $credential, string $optionsJson): PasskeyVerificationResult
            {
                if ($this->failFinish) {
                    throw new RuntimeException('verification failed');
                }

                return new PasskeyVerificationResult('credential-id', 'public-key', 123);
            }

            public function startAuthentication(array $passkeys): PasskeyStartResult
            {
                throw new RuntimeException('unused');
            }

            public function finishAuthentication(array $credential, string $optionsJson, AdminUserPasskey $passkey, string $userHandle): PasskeyVerificationResult
            {
                throw new RuntimeException('unused');
            }
        });
    }

    private function saveToken(string $plainToken, string $email): string
    {
        $id = $this->generateUuid();
        $hashedToken = $this->app->make(TokenHasherInterface::class)->hash($plainToken);

        $this->app->make(RegistrationTokenRepositoryInterface::class)->save(new RegistrationToken(
            RegistrationTokenId::reconstruct($id),
            HashedTokenValue::reconstruct($hashedToken),
            Email::reconstruct($email),
            Role::General,
            Permissions::reconstruct([]),
            ExpiredAt::reconstruct(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        ));

        return $id;
    }
}
