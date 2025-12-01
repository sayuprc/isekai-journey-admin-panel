<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken;

    public function save(RefreshToken $refreshToken): RefreshToken;
}
