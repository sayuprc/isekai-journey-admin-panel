<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

use Auth\Domain\Models\AdminUserPasskey;

interface PasskeyAuthenticatorInterface
{
    /**
     * @param list<AdminUserPasskey> $excludePasskeys
     */
    public function startRegistration(
        string $userHandle,
        string $userName,
        string $displayName,
        array $excludePasskeys = [],
    ): PasskeyStartResult;

    /**
     * @param array<string, mixed> $credential
     */
    public function finishRegistration(array $credential, string $optionsJson): PasskeyRegistrationResult;

    /**
     * @param list<AdminUserPasskey> $passkeys
     */
    public function startAuthentication(array $passkeys): PasskeyStartResult;

    /**
     * @param array<string, mixed> $credential
     */
    public function credentialId(array $credential): ?string;

    /**
     * @param array<string, mixed> $credential
     */
    public function finishAuthentication(
        array $credential,
        string $optionsJson,
        AdminUserPasskey $passkey,
        string $userHandle,
    ): PasskeyAuthenticationResult;
}
