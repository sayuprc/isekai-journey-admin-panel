<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\AccessToken;

interface AccessTokenFactoryInterface
{
    public function create(string $refreshTokenId): AccessToken;
}
