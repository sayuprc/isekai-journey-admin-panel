<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use AdminUser\Domain\Models\AdminUserRegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationTokenHasherInterface;
use App\Models\AdminUser\AdminUserRegistrationToken;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyVerificationResult;
use Auth\Route\AuthRouteMap;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class PasskeyRegisterTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canRegisterWithPasskey(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $this->app->instance(PasskeyAuthenticatorInterface::class, new class () implements PasskeyAuthenticatorInterface {
            public function startRegistration(string $userHandle, string $userName, string $displayName): PasskeyStartResult
            {
                return new PasskeyStartResult('{"challenge":"register"}', ['challenge' => 'register']);
            }

            public function finishRegistration(array $credential, string $optionsJson): PasskeyVerificationResult
            {
                return new PasskeyVerificationResult('credential-1', 'public-key-1', 0);
            }

            public function startAuthentication(array $passkeys): PasskeyStartResult
            {
                return new PasskeyStartResult('{"challenge":"login"}', ['challenge' => 'login']);
            }

            public function finishAuthentication(
                array $credential,
                string $optionsJson,
                AdminUserPasskey $passkey,
                string $userHandle,
            ): PasskeyVerificationResult {
                return new PasskeyVerificationResult($passkey->credentialId, $passkey->publicKey, $passkey->signCount + 1);
            }
        });

        $plainToken = 'plain-registration-token';
        $tokenHash = $this->app->make(RegistrationTokenHasherInterface::class)->hash($plainToken);

        $this->app->make(AdminUserRegistrationTokenRepositoryInterface::class)->save(
            $this->createAdminUserRegistrationToken(
                $this->generateUuid(),
                '登録ユーザー',
                'register@example.com',
                Role::General,
                $tokenHash,
                new DateTimeImmutable('+1 hour'),
            ),
        );

        $start = $this->postJson(route(AuthRouteMap::RegisterStart), [
            'email' => 'register@example.com',
            'registrationToken' => $plainToken,
        ])->assertStatus(200)
            ->assertJsonStructure(['authCeremonyId', 'publicKey']);

        $authCeremonyId = $start->json('authCeremonyId');

        $this->postJson(route(AuthRouteMap::RegisterFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => new stdClass(),
        ])->assertStatus(200)
            ->assertJsonStructure(['accessToken', 'refreshTokenId', 'refreshToken']);

        $this->assertDatabaseHas('admin_users', [
            'email' => 'register@example.com',
            'role' => Role::General->value,
        ]);
        $this->assertDatabaseHas('admin_user_passkeys', ['credential_id' => 'credential-1']);
        $this->assertDatabaseHas('admin_user_registration_tokens', [
            'email' => 'register@example.com',
        ]);
        $this->assertNotNull(
            AdminUserRegistrationToken::query()
                ->where('email', 'register@example.com')
                ->firstOrFail()
                ->used_at,
        );
    }
}
