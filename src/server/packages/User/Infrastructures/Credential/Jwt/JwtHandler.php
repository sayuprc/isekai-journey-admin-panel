<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use User\Domain\Services\Jwt\AccessTokenPayload;
use User\Domain\Services\Jwt\JwtHandlerInterface;

class JwtHandler implements JwtHandlerInterface
{
    private readonly string $alg;

    private readonly string $key;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly MapperInterface $mapper,
        ConfigInterface $config,
    ) {
        $this->alg = $config->getString('auth.jwt.alg');
        $this->key = $config->getString('auth.jwt.key');
    }

    public function generate(AccessTokenPayload $payload): string
    {
        return JWT::encode($payload->toArray(), $this->key, $this->alg);
    }

    public function decode(string $jwt): AccessTokenPayload
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        return $this->mapper->map(AccessTokenPayload::class, JWT::decode($jwt, new Key($this->key, $this->alg)));
    }
}
