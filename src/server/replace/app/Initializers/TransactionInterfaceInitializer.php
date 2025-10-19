<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\TransactionInterface;
use Support\DebugInfrastructures\NopTransaction;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class TransactionInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): TransactionInterface
    {
        return new NopTransaction();
    }
}
