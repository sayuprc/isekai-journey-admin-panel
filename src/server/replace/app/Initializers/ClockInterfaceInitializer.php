<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\ClockInterface;
use Support\Infrastructures\Clock;
use Tempest\Container\Container;

readonly class ClockInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): ClockInterface
    {
        return $this->resolve($container->get(Clock::class));
    }
}
