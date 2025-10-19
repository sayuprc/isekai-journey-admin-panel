<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use User\Infrastructures\Credential\AccessToken\AccessTokenFactory;

readonly class AccessTokenFactoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): AccessTokenFactoryInterface
    {
        return $container->get(AccessTokenFactory::class);
    }
}
