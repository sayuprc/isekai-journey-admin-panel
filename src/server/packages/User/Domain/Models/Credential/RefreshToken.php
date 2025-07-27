<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

readonly class RefreshToken
{
    public function __construct(
        public TokenValue $token,
        public ExpiredAt $expiredAt,
    ) {
    }
}
