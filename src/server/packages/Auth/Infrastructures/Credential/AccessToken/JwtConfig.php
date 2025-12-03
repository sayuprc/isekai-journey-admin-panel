<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use Support\Contracts\ConfigInterface;

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
