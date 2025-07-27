<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\Jwt;

use Support\Contracts\ConfigInterface;
use User\Domain\Services\Jwt\JwtConfigInterface;

readonly class JwtConfig implements JwtConfigInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    public function issuer(): string
    {
        return $this->config->getString('app.url');
    }
}
