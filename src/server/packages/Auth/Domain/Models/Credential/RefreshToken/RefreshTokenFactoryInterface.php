<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use User\Domain\Models\UserId;

interface RefreshTokenFactoryInterface
{
    public function create(
        RefreshTokenId $refreshTokenId,
        UserId $userId,
        TokenValue $token,
        ExpiredAt $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken;
}
