<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Models\Credential\AccessToken\AccessToken;
use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Credential\AccessToken\Jwt;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;

readonly class AccessTokenFactory implements AccessTokenFactoryInterface
{
    public function __construct(private JwtHandlerInterface $jwt)
    {
    }

    public function create(AccessTokenPayload $payload): AccessToken
    {
        return new AccessToken(Jwt::reconstruct($this->jwt->generate($payload)));
    }
}
