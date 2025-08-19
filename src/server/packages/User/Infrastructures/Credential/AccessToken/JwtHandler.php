<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\AccessToken;

use Firebase\JWT\ExpiredException as LibExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use User\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use User\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use User\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;
use User\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;

readonly class JwtHandler implements JwtHandlerInterface
{
    private string $alg;

    private string $key;

    public function __construct(
        private ClockInterface $clock,
        private MapperInterface $mapper,
        private JwtConfigInterface $jwtConfig,
        ConfigInterface $config,
    ) {
        $this->alg = $config->getString('auth.jwt.alg');
        $this->key = $config->getString('auth.jwt.key');
    }

    public function generate(AccessTokenPayload $payload): string
    {
        return JWT::encode($payload->toArray(), $this->key, $this->alg);
    }

    public function verify(string $jwt): AccessTokenPayload
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($jwt, new Key($this->key, $this->alg));
        } catch (LibExpiredException $e) {
            throw new ExpiredException(previous: $e);
        }

        $payload = $this->mapper->map(AccessTokenPayload::class, $decoded);

        if ($payload->iss !== $this->jwtConfig->issuer()) {
            throw new InvalidIssuerException(sprintf('不正なissが設定されている[%s]', $payload->iss));
        }

        return $payload;
    }
}
