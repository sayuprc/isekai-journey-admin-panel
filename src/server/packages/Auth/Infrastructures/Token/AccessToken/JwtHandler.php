<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\AccessToken;

use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtConfig;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\Domain\Error\EntityRuleViolationError;
readonly class JwtHandler implements JwtHandlerInterface
{
    public function __construct(
        private ClockInterface $clock,
        private MapperInterface $mapper,
        private JwtConfig $config,
    ) {
    }

    #[Override]
    public function generate(AccessTokenPayload $payload): string
    {
        return JWT::encode($payload->toArray(), $this->config->key, $this->config->alg);
    }

    #[Override]
    public function verify(string $jwt): Result
    {
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($jwt, new Key($this->config->key, $this->config->alg));
        } catch (ExpiredException $e) {
            return new Err(new EntityRuleViolationError('exp', $e->getMessage()));
        }

        $payload = $this->mapper->map(AccessTokenPayload::class, $decoded);

        if ($payload->iss !== $this->config->issuer) {
            return new Err(new EntityRuleViolationError('iss', sprintf('不正なissが設定されている[%s]', $payload->iss)));
        }

        return new Ok($payload);
    }
}
