<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\Register;

use Auth\Domain\Models\Token\AccessToken\AccessToken;

readonly class RegisterOutputData
{
    public function __construct(
        public AccessToken $accessToken,
        public string $refreshTokenId,
        public string $plainRefreshToken,
    ) {
    }
}
