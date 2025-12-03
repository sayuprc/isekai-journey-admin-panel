<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use Auth\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * @throws ExpiredException
     * @throws InvalidIssuerException
     */
    public function verify(string $jwt): AccessTokenPayload;
}
