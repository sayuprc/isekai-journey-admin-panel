<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Credential\AccessToken;

use ResultType\Result;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * @return Result<AccessTokenPayload, string>
     */
    public function verify(string $jwt): Result;
}
