<?php

declare(strict_types=1);

namespace App\Initializers;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Infrastructures\CreatorFactory;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class CreatorFactoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): CreatorFactoryInterface
    {
        return $container->get(CreatorFactory::class);
    }
}
