<?php

declare(strict_types=1);

namespace User\Domain\Services\Credential\AccessToken;

use User\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use User\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * @throws ExpiredException
     * @throws InvalidIssuerException
     */
    public function verify(string $jwt): AccessTokenPayload;
}
