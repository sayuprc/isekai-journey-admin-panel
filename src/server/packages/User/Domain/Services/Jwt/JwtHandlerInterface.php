<?php

declare(strict_types=1);

namespace User\Domain\Services\Jwt;

use User\Domain\Services\Jwt\Exceptions\ExpiredException;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * @throws ExpiredException
     */
    public function decode(string $jwt): AccessTokenPayload;
}
