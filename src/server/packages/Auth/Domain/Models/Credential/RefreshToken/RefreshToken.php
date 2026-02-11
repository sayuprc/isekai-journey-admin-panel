<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use DateTimeInterface;

readonly class RefreshToken
{
    public function __construct(
        public RefreshTokenId $refreshTokenId,
        public AdminUserId $userId,
        public TokenValue $token,
        private ExpiredAt $expiredAt,
        private ConsumptionStatus $status,
    ) {
    }

    public function isAvailable(DateTimeInterface $now): bool
    {
        return $this->status->isAvailable() && ! $this->expiredAt->isExpired($now);
    }
}
