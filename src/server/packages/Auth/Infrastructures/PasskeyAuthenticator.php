<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Services\PasskeyAuthenticationResult;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
use Auth\Domain\Services\PasskeyStartResult;
use Cose\Algorithms;
use InvalidArgumentException;
use Override;
use ParagonIE\ConstantTime\Base64UrlSafe;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Throwable;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

readonly class PasskeyAuthenticator implements PasskeyAuthenticatorInterface
{
    private DenormalizerInterface&NormalizerInterface&Serializer $serializer;

    public function __construct(private PasskeyCredentialRecordConverter $credentialRecordConverter)
    {
        $serializer = new WebauthnSerializerFactory(AttestationStatementSupportManager::create())->create();
        assert($serializer instanceof Serializer);
        $this->serializer = $serializer;
    }

    /**
     * @param list<AdminUserPasskey> $excludePasskeys
     */
    #[Override]
    public function startRegistration(
        string $userHandle,
        string $userName,
        string $displayName,
        array $excludePasskeys = [],
    ): PasskeyStartResult {
        // origin 設定の不備をセレモニー開始時点で fail-fast する (finish 側は例外が握り潰されるため)
        $this->host();

        $excludeCredentials = array_map(
            fn (AdminUserPasskey $passkey): PublicKeyCredentialDescriptor => $this->toDescriptor($passkey),
            $excludePasskeys,
        );
        $timeout = config('auth.passkey.timeout_ms');

        $options = PublicKeyCredentialCreationOptions::create(
            PublicKeyCredentialRpEntity::create(
                config()->string('auth.passkey.rp_name'),
                config()->string('auth.passkey.rp_id'),
            ),
            PublicKeyCredentialUserEntity::create($userName, $userHandle, $displayName),
            random_bytes(32),
            [
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_RS256),
            ],
            AuthenticatorSelectionCriteria::create(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
            ),
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $excludeCredentials,
            is_int($timeout) && $timeout > 0 ? $timeout : 60000,
        );

        /** @var array<string, mixed> $publicKey */
        $publicKey = $this->serializer->normalize($options, JsonEncoder::FORMAT);

        return new PasskeyStartResult(
            $this->serializer->serialize($options, JsonEncoder::FORMAT),
            $publicKey,
        );
    }

    /**
     * @param array<string, mixed> $credential
     */
    #[Override]
    public function finishRegistration(array $credential, string $optionsJson): PasskeyRegistrationResult
    {
        $publicKeyCredential = $this->deserializeCredential($credential);
        $response = $publicKeyCredential->response;

        if (! $response instanceof AuthenticatorAttestationResponse) {
            throw new InvalidArgumentException('AuthenticatorAttestationResponse ではありません');
        }

        /** @var PublicKeyCredentialCreationOptions $options */
        $options = $this->serializer->deserialize(
            $optionsJson,
            PublicKeyCredentialCreationOptions::class,
            JsonEncoder::FORMAT,
        );

        $factory = new CeremonyStepManagerFactory();
        $factory->setAllowedOrigins([config()->string('auth.passkey.origin')]);

        $credentialRecord = AuthenticatorAttestationResponseValidator::create($factory->creationCeremony())
            ->check($response, $options, $this->host());

        if (! hash_equals($publicKeyCredential->rawId, $credentialRecord->publicKeyCredentialId)) {
            throw new InvalidArgumentException('Credential ID が一致しません');
        }

        return new PasskeyRegistrationResult(
            $credentialRecord->publicKeyCredentialId,
            $credentialRecord->credentialPublicKey,
            $credentialRecord->userHandle,
            $credentialRecord->aaguid->toRfc4122(),
            array_values($credentialRecord->transports),
            $credentialRecord->backupEligible,
            $credentialRecord->backupStatus,
            $credentialRecord->counter,
        );
    }

    #[Override]
    public function startAuthentication(): PasskeyStartResult
    {
        // origin 設定の不備をセレモニー開始時点で fail-fast する (finish 側は例外が握り潰されるため)
        $this->host();

        $timeout = config('auth.passkey.timeout_ms');

        // ユーザー列挙を防ぐため allowCredentials は空にする。登録時に residentKey を
        // 必須にしているため、discoverable credential でログインが成立する。
        $options = PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            config()->string('auth.passkey.rp_id'),
            [],
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            is_int($timeout) && $timeout > 0 ? $timeout : 60000,
        );

        /** @var array<string, mixed> $publicKey */
        $publicKey = $this->serializer->normalize($options, JsonEncoder::FORMAT);

        return new PasskeyStartResult(
            $this->serializer->serialize($options, JsonEncoder::FORMAT),
            $publicKey,
        );
    }

    /**
     * @param array<string, mixed> $credential
     */
    #[Override]
    public function credentialId(array $credential): ?string
    {
        $rawId = $credential['rawId'] ?? null;

        if (is_string($rawId) && $rawId !== '') {
            try {
                return Base64UrlSafe::decodeNoPadding($rawId);
            } catch (Throwable) {
                return null;
            }
        }

        $id = $credential['id'] ?? null;

        if (! is_string($id) || $id === '') {
            return null;
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $credential
     */
    #[Override]
    public function finishAuthentication(
        array $credential,
        string $optionsJson,
        AdminUserPasskey $passkey,
        string $userHandle,
    ): PasskeyAuthenticationResult {
        $publicKeyCredential = $this->deserializeCredential($credential);
        $response = $publicKeyCredential->response;

        if (! $response instanceof AuthenticatorAssertionResponse) {
            throw new InvalidArgumentException('AuthenticatorAssertionResponse ではありません');
        }

        /** @var PublicKeyCredentialRequestOptions $options */
        $options = $this->serializer->deserialize(
            $optionsJson,
            PublicKeyCredentialRequestOptions::class,
            JsonEncoder::FORMAT,
        );

        $factory = new CeremonyStepManagerFactory();
        $factory->setAllowedOrigins([config()->string('auth.passkey.origin')]);

        $credentialRecord = AuthenticatorAssertionResponseValidator::create($factory->requestCeremony())
            ->check(
                $this->credentialRecordConverter->toCredentialRecord($passkey),
                $response,
                $options,
                $this->host(),
                $userHandle,
            );

        return new PasskeyAuthenticationResult(
            $publicKeyCredential->rawId,
            $credentialRecord->counter,
        );
    }

    /**
     * @param array<string, mixed> $credential
     */
    private function deserializeCredential(array $credential): PublicKeyCredential
    {
        /** @var PublicKeyCredential */
        return $this->serializer->denormalize(
            $credential,
            PublicKeyCredential::class,
            JsonEncoder::FORMAT,
        );
    }

    private function toDescriptor(AdminUserPasskey $passkey): PublicKeyCredentialDescriptor
    {
        return PublicKeyCredentialDescriptor::create(
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            $passkey->credentialId,
            $passkey->transports,
        );
    }

    private function host(): string
    {
        $origin = config()->string('auth.passkey.origin');
        $host = parse_url($origin, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            throw new RuntimeException(sprintf('auth.passkey.origin からホストを取得できません: [%s]', $origin));
        }

        return $host;
    }
}
