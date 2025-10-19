<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\ClockInterface;
use Support\Infrastructures\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class ClockInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): ClockInterface
    {
        return new Clock();
    }
}
