<?php

declare(strict_types=1);

namespace App\Initializers;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Tempest\Container\Container;

readonly class CreatorRepositoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): CreatorRepositoryInterface
    {
        return $this->resolve($container->get(FileCreatorRepository::class));
    }
}
