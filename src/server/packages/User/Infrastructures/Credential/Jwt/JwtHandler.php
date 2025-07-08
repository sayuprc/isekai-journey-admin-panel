<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\Jwt;

use Firebase\JWT\JWT;
use Support\Contracts\ConfigInterface;
use User\Domain\Services\Jwt\JwtHandlerInterface;
use User\Domain\Services\Jwt\Payload;

class JwtHandler implements JwtHandlerInterface
{
    private readonly string $alg;

    private readonly string $key;

    public function __construct(ConfigInterface $config)
    {
        $this->alg = $config->getString('auth.jwt.alg');
        $this->key = $config->getString('auth.jwt.key');
    }

    public function generate(Payload $payload): string
    {
        return JWT::encode($payload->toArray(), $this->key, $this->alg);
    }
}
