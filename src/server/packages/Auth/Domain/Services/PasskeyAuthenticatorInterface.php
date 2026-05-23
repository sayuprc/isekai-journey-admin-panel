<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

use Auth\Domain\Models\AdminUserPasskey;

interface PasskeyAuthenticatorInterface
{
    public function startRegistration(
        string $userHandle,
        string $userName,
        string $displayName,
    ): PasskeyStartResult;

    /**
     * @param array<string, mixed> $credential
     */
    public function finishRegistration(array $credential, string $optionsJson): PasskeyVerificationResult;

    /**
     * @param list<AdminUserPasskey> $passkeys
     */
    public function startAuthentication(array $passkeys): PasskeyStartResult;

    /**
     * @param array<string, mixed> $credential
     */
    public function finishAuthentication(
        array $credential,
        string $optionsJson,
        AdminUserPasskey $passkey,
        string $userHandle,
    ): PasskeyVerificationResult;
}
