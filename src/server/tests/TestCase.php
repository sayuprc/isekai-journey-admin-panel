<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Support\Contracts\UuidGeneratorInterface;

abstract class TestCase extends BaseTestCase
{
    protected function generateUuid(): string
    {
        return $this->app->make(UuidGeneratorInterface::class)->generate();
    }
}
