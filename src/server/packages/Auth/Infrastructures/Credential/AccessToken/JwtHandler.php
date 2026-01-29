<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;

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

    public function verify(string $jwt): Result
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($jwt, new Key($this->key, $this->alg));
        } catch (ExpiredException $e) {
            return new Err($e->getMessage());
        }

        $payload = $this->mapper->map(AccessTokenPayload::class, $decoded);

        if ($payload->iss !== $this->jwtConfig->issuer()) {
            return new Err(sprintf('不正なissが設定されている[%s]', $payload->iss));
        }

        return new Ok($payload);
    }
}
