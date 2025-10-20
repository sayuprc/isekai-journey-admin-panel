<?php

declare(strict_types=1);

namespace App\Initializers;

use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Infrastructures\SongTypeFactory;
use Tempest\Container\Container;

readonly class SongTypeFactoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): SongTypeFactoryInterface
    {
        return $this->resolve($container->get(SongTypeFactory::class));
    }
}
