<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use User\Infrastructures\Credential\AccessToken\AccessTokenFactory;

readonly class AccessTokenFactoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): AccessTokenFactoryInterface
    {
        return $this->resolve($container->get(AccessTokenFactory::class));
    }
}
