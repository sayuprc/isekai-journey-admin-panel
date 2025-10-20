<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\UuidGeneratorInterface;
use Support\Infrastructures\UuidGenerator;
use Tempest\Container\Container;

readonly class UuidGeneratorInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): UuidGeneratorInterface
    {
        return $this->resolve($container->get(UuidGenerator::class));
    }
}
