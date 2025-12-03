<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

interface RefreshTokenFactoryInterface
{
    public function create(string $userId): RefreshToken;
}
