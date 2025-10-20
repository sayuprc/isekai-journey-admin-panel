<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use User\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use User\Infrastructures\Credential\RefreshToken\RandomTokenGenerator;

readonly class RandomTokenGeneratorInterfaceInitializer extends Initializer
{
    public function initialize(Container $container): RandomTokenGeneratorInterface
    {
        return $this->resolve($container->get(RandomTokenGenerator::class));
    }
}
