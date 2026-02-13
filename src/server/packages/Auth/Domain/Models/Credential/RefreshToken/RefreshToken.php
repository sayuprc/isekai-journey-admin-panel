<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use DateTimeImmutable;
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

    public static function reconstruct(
        string $refreshTokenId,
        string $userId,
        string $token,
        DateTimeImmutable $expiredAt,
        int $status,
    ): self {
        return new self(
            RefreshTokenId::reconstruct($refreshTokenId),
            AdminUserId::reconstruct($userId),
            TokenValue::reconstruct($token),
            ExpiredAt::reconstruct($expiredAt),
            ConsumptionStatus::from($status),
        );
    }

    public function isAvailable(DateTimeInterface $now): bool
    {
        return $this->status->isAvailable() && ! $this->expiredAt->isExpired($now);
    }

    /**
     * @return array{refresh_token_id: string, user_id: string, token: string, expired_at: non-falsy-string, status: value-of<ConsumptionStatus>}
     */
    public function toArray(): array
    {
        return [
            'refresh_token_id' => $this->refreshTokenId->value,
            'user_id' => $this->userId->value,
            'token' => $this->token->value,
            'expired_at' => $this->expiredAt->value->format('Y-m-d H:i:s'),
            'status' => $this->status->value,
        ];
    }
}
