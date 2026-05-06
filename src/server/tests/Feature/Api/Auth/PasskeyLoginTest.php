<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyVerificationResult;
use Auth\Route\AuthRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class PasskeyLoginTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canLoginWithPasskey(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $this->app->instance(PasskeyAuthenticatorInterface::class, new class implements PasskeyAuthenticatorInterface {
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
                \Auth\Domain\Models\AdminUserPasskey $passkey,
                string $userHandle,
            ): PasskeyVerificationResult {
                return new PasskeyVerificationResult($passkey->credentialId, $passkey->publicKey, 10);
            }
        });

        $user = $this->createAdminUser($this->generateUuid(), 'login@example.com');
        $this->storeAdminUsers($user);

        $this->app->make(AdminUserPasskeyRepositoryInterface::class)->save(
            new AdminUserPasskey(
                $this->generateUuid(),
                $user->adminUserId->value,
                'credential-1',
                'public-key-1',
                0,
                new \DateTimeImmutable(),
                null,
            ),
        );

        $start = $this->postJson(route(AuthRouteMap::LoginStart), [
            'email' => 'login@example.com',
        ])->assertStatus(200)
            ->assertJsonStructure(['authCeremonyId', 'publicKey']);

        $authCeremonyId = $start->json('authCeremonyId');

        $this->postJson(route(AuthRouteMap::LoginFinish), [
            'authCeremonyId' => $authCeremonyId,
            'credential' => ['id' => 'credential-1'],
        ])->assertStatus(200)
            ->assertJsonStructure(['accessToken', 'refreshTokenId', 'refreshToken']);

        $this->assertDatabaseHas('admin_user_passkeys', [
            'credential_id' => 'credential-1',
            'sign_count' => 10,
        ]);
        $this->assertNotNull(
            \App\Models\AdminUser\AdminUserPasskey::query()
                ->where('credential_id', 'credential-1')
                ->firstOrFail()
                ->last_used_at,
        );
    }
}
