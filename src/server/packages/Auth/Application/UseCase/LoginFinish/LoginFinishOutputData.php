<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\LoginFinish;

use Auth\Domain\Models\Token\AccessToken\AccessToken;

readonly class LoginFinishOutputData
{
    public function __construct(
        public AccessToken $accessToken,
        public string $refreshTokenId,
        public string $plainRefreshToken,
    ) {
    }
}
