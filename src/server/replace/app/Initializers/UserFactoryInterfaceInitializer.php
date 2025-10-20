<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\Domain\Models\UserFactoryInterface;
use User\Infrastructures\UserFactory;

readonly class UserFactoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): UserFactoryInterface
    {
        return $this->resolve($container->get(UserFactory::class));
    }
}
