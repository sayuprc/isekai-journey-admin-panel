<?php

declare(strict_types=1);

namespace App\Initializers;

use Emonkak\Database\PDOInterface;
use Support\Infrastructures\Database\SQLiteConnector;
use Tempest\Container\Container;

readonly class PDOInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): PDOInterface
    {
        return $this->resolve($container->get(SQLiteConnector::class))->connect();
    }
}
