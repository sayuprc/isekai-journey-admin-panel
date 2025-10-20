<?php

declare(strict_types=1);

namespace App\Initializers;

use Tempest\Container\Initializer as InitializerInterface;
use Webmozart\Assert\Assert;

abstract readonly class Initializer implements InitializerInterface
{
    /**
     * @template T
     *
     * @param T|null $value
     *
     * @return T
     */
    protected function resolve(mixed $value): mixed
    {
        Assert::notNull($value);

        return $value;
    }
}
