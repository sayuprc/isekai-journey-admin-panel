<?php

declare(strict_types=1);

namespace App\Initializers;

use SongType\DebugInfrastructures\FileSongTypeRepository;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Tempest\Container\Container;

readonly class SongTypeRepositoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): SongTypeRepositoryInterface
    {
        return $this->resolve($container->get(FileSongTypeRepository::class));
    }
}
