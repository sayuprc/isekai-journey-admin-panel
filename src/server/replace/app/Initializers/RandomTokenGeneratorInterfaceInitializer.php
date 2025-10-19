<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use User\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use User\Infrastructures\Credential\RefreshToken\RandomTokenGenerator;

readonly class RandomTokenGeneratorInterfaceInitializer implements Initializer
{
    public function initialize(Container $container): RandomTokenGeneratorInterface
    {
        return new RandomTokenGenerator();
    }
}
