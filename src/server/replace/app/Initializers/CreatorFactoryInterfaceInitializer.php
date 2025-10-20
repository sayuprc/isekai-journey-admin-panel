<?php

declare(strict_types=1);

namespace App\Initializers;

use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Infrastructures\CreatorFactory;
use Tempest\Container\Container;

readonly class CreatorFactoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): CreatorFactoryInterface
    {
        return $this->resolve($container->get(CreatorFactory::class));
    }
}
