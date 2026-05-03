<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Refresh;

readonly class RefreshInputData
{
    public function __construct(
        public string $refreshTokenId,
        public string $refreshToken,
    ) {
    }
}
