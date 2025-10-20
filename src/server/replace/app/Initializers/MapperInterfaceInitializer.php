<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\MapperInterface;
use Support\Infrastructures\Mapper;
use Tempest\Container\Container;

readonly class MapperInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): MapperInterface
    {
        return $this->resolve($container->get(Mapper::class));
    }
}
