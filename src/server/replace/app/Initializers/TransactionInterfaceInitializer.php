<?php

declare(strict_types=1);

namespace App\Initializers;

use Support\Contracts\TransactionInterface;
use Support\DebugInfrastructures\NopTransaction;
use Tempest\Container\Container;

readonly class TransactionInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): TransactionInterface
    {
        return $this->resolve($container->get(NopTransaction::class));
    }
}
