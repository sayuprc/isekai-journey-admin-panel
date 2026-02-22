<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;

interface RefreshTokenFactoryInterface
{
    public function create(
        RefreshTokenId $refreshTokenId,
        AdminUserId $adminUserId,
        TokenValue $token,
        ExpiredAt $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken;
}
