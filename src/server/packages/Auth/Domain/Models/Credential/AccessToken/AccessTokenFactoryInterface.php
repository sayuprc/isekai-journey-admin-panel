<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\AccessToken;

use ResultType\Result;
use Support\Domain\Validation\ValidationError;

interface AccessTokenFactoryInterface
{
    /**
     * @return Result<AccessToken, array<ValidationError>>
     */
    public function create(string $refreshTokenId): Result;
}
