<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use User\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;

readonly class RefreshTokenFactoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): RefreshTokenFactoryInterface
    {
        return $this->resolve($container->get(RefreshTokenFactory::class));
    }
}
