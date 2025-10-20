<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\DebugInfrastructures\FileUserRepository;
use User\Domain\Models\UserRepositoryInterface;

readonly class UserRepositoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): UserRepositoryInterface
    {
        // TODO DB にアクセスできるようになったら変える
        return $this->resolve($container->get(FileUserRepository::class));
    }
}
