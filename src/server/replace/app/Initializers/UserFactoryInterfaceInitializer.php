<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\Domain\Models\UserFactoryInterface;
use User\Infrastructures\UserFactory;

readonly class UserFactoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): UserFactoryInterface
    {
        return $container->get(UserFactory::class);
    }
}
