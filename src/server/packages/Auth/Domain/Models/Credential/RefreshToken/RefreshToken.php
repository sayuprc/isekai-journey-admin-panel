<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use DateTimeInterface;
use User\Domain\Models\UserId;

readonly class RefreshToken
{
    public function __construct(
        public RefreshTokenId $refreshTokenId,
        public UserId $userId,
        public TokenValue $token,
        private ExpiredAt $expiredAt,
        private ConsumptionStatus $status,
    ) {
    }

    public function isAvailable(DateTimeInterface $now): bool
    {
        return $this->status->isAvailable() && ! $this->expiredAt->isPast($now);
    }
}
