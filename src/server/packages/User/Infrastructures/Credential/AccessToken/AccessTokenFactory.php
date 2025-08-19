<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\AccessToken;

use Support\Contracts\ClockInterface;
use User\Domain\Models\Credential\AccessToken\AccessToken;
use User\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\AccessToken\Jwt;
use User\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use User\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;

readonly class AccessTokenFactory implements AccessTokenFactoryInterface
{
    private const int TTL_HOUR = 1;

    public function __construct(
        private JwtConfigInterface $config,
        private ClockInterface $clock,
        private JwtHandlerInterface $jwt,
    ) {
    }

    public function create(string $refreshTokenId): AccessToken
    {
        $now = $this->clock->now();

        $payload = new AccessTokenPayload(
            iss: $this->config->issuer(),
            iat: $now->getTimestamp(),
            exp: $now->modify('+' . self::TTL_HOUR . ' hours')->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: $refreshTokenId,
        );

        return new AccessToken(new Jwt($this->jwt->generate($payload)));
    }
}
