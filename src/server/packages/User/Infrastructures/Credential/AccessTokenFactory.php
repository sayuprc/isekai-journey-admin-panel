<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential;

use Support\Contracts\ClockInterface;
use User\Domain\Models\Credential\AccessToken;
use User\Domain\Models\Credential\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\Jwt;
use User\Domain\Services\Jwt\AccessTokenPayload;
use User\Domain\Services\Jwt\JwtConfigInterface;
use User\Domain\Services\Jwt\JwtHandlerInterface;

class AccessTokenFactory implements AccessTokenFactoryInterface
{
    private const int TTL_HOUR = 1;

    public function __construct(
        private readonly JwtConfigInterface $config,
        private readonly ClockInterface $clock,
        private readonly JwtHandlerInterface $jwt,
    ) {
    }

    public function create(string $id): AccessToken
    {
        $now = $this->clock->now();

        $payload = new AccessTokenPayload(
            iss: $this->config->issuer(),
            iat: $now->getTimestamp(),
            exp: $now->modify('+' . self::TTL_HOUR . ' hours')->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: $id,
        );

        return new AccessToken(new Jwt($this->jwt->generate($payload)));
    }
}
