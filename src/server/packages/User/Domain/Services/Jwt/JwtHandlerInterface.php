<?php

declare(strict_types=1);

namespace User\Domain\Services\Jwt;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;
}
