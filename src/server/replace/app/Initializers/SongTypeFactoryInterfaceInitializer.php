<?php

declare(strict_types=1);

namespace App\Initializers;

use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Infrastructures\SongTypeFactory;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class SongTypeFactoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): SongTypeFactoryInterface
    {
        return $container->get(SongTypeFactory::class);
    }
}
