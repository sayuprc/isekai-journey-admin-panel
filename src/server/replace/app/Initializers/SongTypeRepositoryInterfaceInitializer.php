<?php

declare(strict_types=1);

namespace App\Initializers;

use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class SongTypeRepositoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): SongTypeRepositoryInterface
    {
        return $container->get(FileSongTypeRepository::class);
    }
}
