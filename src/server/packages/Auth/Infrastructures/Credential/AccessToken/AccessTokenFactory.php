<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Models\Credential\AccessToken\AccessToken;
use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Credential\AccessToken\Jwt;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Domain\Validation\ValidationError;

readonly class AccessTokenFactory implements AccessTokenFactoryInterface
{
    private const int TTL_HOUR = 1;

    public function __construct(
        private JwtConfigInterface $config,
        private ClockInterface $clock,
        private JwtHandlerInterface $jwt,
    ) {
    }

    public function create(string $refreshTokenId): Result
    {
        $now = $this->clock->now();

        $payload = new AccessTokenPayload(
            iss: $this->config->issuer(),
            iat: $now->getTimestamp(),
            exp: $now->modify('+' . self::TTL_HOUR . ' hours')->getTimestamp(),
            nbf: $now->getTimestamp(),
            jti: $refreshTokenId,
        );

        return Jwt::create($this->jwt->generate($payload))
            ->map(fn (Jwt $jwt): AccessToken => new AccessToken($jwt))
            ->mapErr(fn (ValidationError $error): array => [$error]);
    }
}
