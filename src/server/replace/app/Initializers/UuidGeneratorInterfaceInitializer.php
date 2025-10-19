<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\UuidGeneratorInterface;
use Support\Infrastructures\UuidGenerator;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class UuidGeneratorInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): UuidGeneratorInterface
    {
        return new UuidGenerator();
    }
}
