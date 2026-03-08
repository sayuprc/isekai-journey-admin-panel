<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

use Auth\Domain\Models\Token\AccessToken\AccessToken;

readonly class LoginOutputData
{
    public function __construct(
        public AccessToken $accessToken,
        public string $plainRefreshToken,
    ) {
    }
}
