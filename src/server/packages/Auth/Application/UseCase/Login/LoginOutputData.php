<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

use Auth\Domain\Models\Credential\AccessToken\AccessToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;

readonly class LoginOutputData
{
    public function __construct(
        public AccessToken $accessToken,
        public RefreshToken $refreshToken,
    ) {
    }
}
