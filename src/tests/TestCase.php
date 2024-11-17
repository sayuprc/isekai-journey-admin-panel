<?php

declare(strict_types=1);

namespace Tests;

use App\Shared\Application\Uuid\DummyUuidGenerator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function generateUuid(): string
    {
        return (new DummyUuidGenerator())->generate();
    }
}
