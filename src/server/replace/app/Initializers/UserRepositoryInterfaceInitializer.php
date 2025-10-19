<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\UserRepositoryInterface;

readonly class UserRepositoryInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): UserRepositoryInterface
    {
        // TODO DB にアクセスできるようになったら変える
        return $container->get(FileUserRepository::class);
    }
}
