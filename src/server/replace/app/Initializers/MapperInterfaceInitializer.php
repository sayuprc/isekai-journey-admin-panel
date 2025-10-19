<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\MapperInterface;
use Support\Infrastructures\Mapper;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class MapperInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): MapperInterface
    {
        return $container->get(Mapper::class);
    }
}
