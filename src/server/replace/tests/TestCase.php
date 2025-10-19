<?php

declare(strict_types=1);

namespace Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Support\Contracts\UuidGeneratorInterface;

abstract class TestCase extends IntegrationTestCase
{
    use MockeryPHPUnitIntegration;

    protected function generateUuid(): string
    {
        return $this->container->get(UuidGeneratorInterface::class)->generate();
    }
}
