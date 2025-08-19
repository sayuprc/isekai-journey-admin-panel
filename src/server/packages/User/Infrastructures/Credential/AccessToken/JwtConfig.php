<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\AccessToken;

use Support\Contracts\ConfigInterface;
use User\Domain\Services\Credential\AccessToken\JwtConfigInterface;

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
