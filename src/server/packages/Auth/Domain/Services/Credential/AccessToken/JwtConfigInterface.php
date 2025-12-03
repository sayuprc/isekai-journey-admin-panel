<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Credential\AccessToken;

interface JwtConfigInterface
{
    public function issuer(): string;
}
