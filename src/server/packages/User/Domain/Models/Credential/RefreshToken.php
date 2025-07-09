<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

class RefreshToken
{
    public function __construct(
        public readonly TokenValue $token,
        public readonly ExpiredAt $expiredAt,
    ) {
    }
}
