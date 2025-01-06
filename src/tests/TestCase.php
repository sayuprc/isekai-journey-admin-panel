<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Shared\Application\Uuid\DummyUuidGenerator;

abstract class TestCase extends BaseTestCase
{
    protected function generateUuid(): string
    {
        return (new DummyUuidGenerator())->generate();
    }
}
