<?php

declare(strict_types=1);

namespace Support\ResultType;

use LogicException;

/**
 * @template-covariant E
 *
 * @template-implements Result<never, E>
 */
class Err implements Result
{
    /**
     * @param E $value
     */
    public function __construct(private readonly mixed $value)
    {
    }

    public function isOk(): bool
    {
        return false;
    }

    public function isErr(): bool
    {
        return true;
    }

    public function unwrap(): mixed
    {
        throw new LogicException('Cannot get value from Err result');
    }

    /**
     * @return E
     */
    public function unwrapErr(): mixed
    {
        return $this->value;
    }
}
