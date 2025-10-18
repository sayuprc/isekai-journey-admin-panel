<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\AccessToken;

use Firebase\JWT\ExpiredException as LibExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use User\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use User\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use User\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;
use User\Domain\Services\Credential\AccessToken\JwtConfig;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;

readonly class JwtHandler implements JwtHandlerInterface
{
    public function __construct(
        private ClockInterface $clock,
        private MapperInterface $mapper,
        private JwtConfig $config,
    ) {
    }

    public function generate(AccessTokenPayload $payload): string
    {
        return JWT::encode($payload->toArray(), $this->config->key, $this->config->alg);
    }

    public function verify(string $jwt): AccessTokenPayload
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($jwt, new Key($this->config->key, $this->config->alg));
        } catch (LibExpiredException $e) {
            throw new ExpiredException(previous: $e);
        }

        $payload = $this->mapper->map(AccessTokenPayload::class, $decoded);

        if ($payload->iss !== $this->config->iss) {
            throw new InvalidIssuerException(sprintf('不正なissが設定されている[%s]', $payload->iss));
        }

        return $payload;
    }
}
