<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\Domain\Services\HasherInterface;
use User\Infrastructures\Hasher;

readonly class HasherInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): HasherInterface
    {
        return new Hasher();
    }
}
