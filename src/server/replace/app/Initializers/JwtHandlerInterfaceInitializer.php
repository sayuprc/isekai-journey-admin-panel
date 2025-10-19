<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use User\Infrastructures\Credential\AccessToken\JwtHandler;

readonly class JwtHandlerInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): JwtHandlerInterface
    {
        return $container->get(JwtHandler::class);
    }
}
