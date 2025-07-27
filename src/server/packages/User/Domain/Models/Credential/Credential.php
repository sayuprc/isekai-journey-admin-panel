<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

use User\Domain\Models\UserId;

readonly class Credential
{
    public function __construct(
        public CredentialId $credentialId,
        public UserId $userId,
        public AccessToken $accessToken,
        public RefreshToken $refreshToken,
        private IsEnabled $isEnabled,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled->value;
    }
}
