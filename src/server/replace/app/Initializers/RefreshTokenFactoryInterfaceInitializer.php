<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use User\Infrastructures\Credential\RefreshToken\RefreshTokenFactory;

readonly class RefreshTokenFactoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): RefreshTokenFactoryInterface
    {
        return $container->get(RefreshTokenFactory::class);
    }
}
