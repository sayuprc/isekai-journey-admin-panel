<?php

declare(strict_types=1);

namespace App\Initializers;

use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

readonly class CreatorRepositoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): CreatorRepositoryInterface
    {
        return $container->get(FileCreatorRepository::class);
    }
}
