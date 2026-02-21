<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\AccessToken;

use ResultType\Result;
use Support\Domain\Error\DomainError;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * @return Result<AccessTokenPayload, DomainError>
     */
    public function verify(string $jwt): Result;
}
