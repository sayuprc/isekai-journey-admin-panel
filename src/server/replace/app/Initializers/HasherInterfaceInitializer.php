<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\Domain\Services\HasherInterface;
use User\Infrastructures\Hasher;

readonly class HasherInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): HasherInterface
    {
        return $this->resolve($container->get(Hasher::class));
    }
}
