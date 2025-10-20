<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use User\Infrastructures\Credential\AccessToken\JwtHandler;

readonly class JwtHandlerInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): JwtHandlerInterface
    {
        return $this->resolve($container->get(JwtHandler::class));
    }
}
