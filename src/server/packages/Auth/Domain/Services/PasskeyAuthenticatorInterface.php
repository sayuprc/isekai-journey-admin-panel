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
     * ユーザー列挙を防ぐため allowCredentials は含めない (discoverable credential を利用)。
     * 入力メールの実在有無に依らず同一形状のオプションを返す。
     */
    public function startAuthentication(): PasskeyStartResult;

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
