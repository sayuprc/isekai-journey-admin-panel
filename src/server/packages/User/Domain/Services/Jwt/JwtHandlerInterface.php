<?php

declare(strict_types=1);

namespace User\Domain\Services\Jwt;

use User\Domain\Services\Jwt\Exceptions\ExpiredException;
use User\Domain\Services\Jwt\Exceptions\InvalidIssuerException;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * @throws ExpiredException
     * @throws InvalidIssuerException
     */
    public function verify(string $jwt): AccessTokenPayload;
}
