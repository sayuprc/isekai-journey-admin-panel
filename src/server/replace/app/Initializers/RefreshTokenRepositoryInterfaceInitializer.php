<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\DebugInfrastructures\FileRefreshTokenRepository;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;

readonly class RefreshTokenRepositoryInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): RefreshTokenRepositoryInterface
    {
        // TODO DB にアクセスできるようになったら変える
        return $this->resolve($container->get(FileRefreshTokenRepository::class));
    }
}
