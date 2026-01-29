<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use ResultType\Result;
use Support\Domain\Validation\ValidationError;

interface RefreshTokenFactoryInterface
{
    /**
     * @return Result<RefreshToken, array<ValidationError>>
     */
    public function create(string $userId): Result;
}
