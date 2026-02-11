<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;

readonly class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    public function create(
        RefreshTokenId $refreshTokenId,
        AdminUserId $userId,
        TokenValue $token,
        ExpiredAt $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken($refreshTokenId, $userId, $token, $expiredAt, $status);
    }
}
