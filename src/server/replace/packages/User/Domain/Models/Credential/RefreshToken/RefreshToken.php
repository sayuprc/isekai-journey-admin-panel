<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential\RefreshToken;

use DateTimeInterface;
use User\Domain\Models\UserId;

readonly class RefreshToken
{
    public function __construct(
        public RefreshTokenId $refreshTokenId,
        public UserId $userId,
        public TokenValue $token,
        private ExpiredAt $expiredAt,
        private IsUsed $isUsed,
    ) {
    }

    public function isEnabled(DateTimeInterface $now): bool
    {
        return ! $this->isUsed->value && ! $this->expiredAt->isPast($now);
    }
}
