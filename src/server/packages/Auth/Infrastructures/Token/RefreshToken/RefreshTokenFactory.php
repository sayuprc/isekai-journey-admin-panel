<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\HashedTokenValue;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Override;

readonly class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    #[Override]
    public function create(
        RefreshTokenId $refreshTokenId,
        AdminUserId $adminUserId,
        HashedTokenValue $token,
        ExpiredAt $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken($refreshTokenId, $adminUserId, $token, $expiredAt, $status);
    }
}
