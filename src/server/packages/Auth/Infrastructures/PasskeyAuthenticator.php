<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyVerificationResult;
use Cose\Algorithms;
use InvalidArgumentException;
use Override;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
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
    /**
     * @var Serializer&NormalizerInterface&DenormalizerInterface
     */
    private Serializer $serializer;

    public function __construct(
        private PasskeyCredentialRecordConverter $credentialRecordConverter,
    ) {
        $serializer = new WebauthnSerializerFactory(AttestationStatementSupportManager::create())->create();
        assert($serializer instanceof Serializer);
        $this->serializer = $serializer;
    }

    #[Override]
    public function startRegistration(
        string $userHandle,
        string $userName,
        string $displayName,
    ): PasskeyStartResult {
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
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
            ),
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            [],
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
    public function finishRegistration(array $credential, string $optionsJson): PasskeyVerificationResult
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

        return new PasskeyVerificationResult(
            Base64UrlSafe::encodeUnpadded($publicKeyCredential->rawId),
            Base64UrlSafe::encodeUnpadded($credentialRecord->credentialPublicKey),
            $credentialRecord->counter,
        );
    }

    /**
     * @param list<AdminUserPasskey> $passkeys
     */
    #[Override]
    public function startAuthentication(array $passkeys): PasskeyStartResult
    {
        $descriptors = array_map(
            fn (AdminUserPasskey $passkey) => PublicKeyCredentialDescriptor::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                Base64UrlSafe::decodeNoPadding($passkey->credentialId),
            ),
            $passkeys,
        );

        $timeout = config('auth.passkey.timeout_ms');

        $options = PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            config()->string('auth.passkey.rp_id'),
            $descriptors,
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
    public function finishAuthentication(
        array $credential,
        string $optionsJson,
        AdminUserPasskey $passkey,
        string $userHandle,
    ): PasskeyVerificationResult {
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

        return new PasskeyVerificationResult(
            Base64UrlSafe::encodeUnpadded($publicKeyCredential->rawId),
            $passkey->publicKey,
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

    private function host(): string
    {
        return parse_url(config()->string('auth.passkey.origin'), PHP_URL_HOST) ?: '';
    }
}
