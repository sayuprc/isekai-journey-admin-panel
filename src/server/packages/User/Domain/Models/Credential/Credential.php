<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

use User\Domain\Models\UserId;

class Credential
{
    public function __construct(
        public readonly CredentialId $credentialId,
        public readonly UserId $userId,
        public readonly AccessToken $accessToken,
        public readonly RefreshToken $refreshToken,
    ) {
    }
}
