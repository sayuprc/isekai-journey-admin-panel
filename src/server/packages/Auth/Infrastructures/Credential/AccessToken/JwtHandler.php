<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfig;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\Domain\Error\DomainRuleViolationError;

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

    public function verify(string $jwt): Result
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($jwt, new Key($this->config->key, $this->config->alg));
        } catch (ExpiredException $e) {
            return new Err(new DomainRuleViolationError('exp', $e->getMessage()));
        }

        $payload = $this->mapper->map(AccessTokenPayload::class, $decoded);

        if ($payload->iss !== $this->config->issuer) {
            return new Err(new DomainRuleViolationError('iss', sprintf('不正なissが設定されている[%s]', $payload->iss)));
        }

        return new Ok($payload);
    }
}
