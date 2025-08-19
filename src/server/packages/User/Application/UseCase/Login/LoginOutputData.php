<?php

declare(strict_types=1);

namespace User\Application\UseCase\Login;

use User\Domain\Models\Credential\AccessToken\AccessToken;
use User\Domain\Models\Credential\RefreshToken\RefreshToken;

readonly class LoginOutputData
{
    public function __construct(
        public AccessToken $accessToken,
        public RefreshToken $refreshToken,
    ) {
    }
}
