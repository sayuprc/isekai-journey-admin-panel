<?php

declare(strict_types=1);

namespace Support\ResultType;

/**
 * @template-covariant T
 * @template-covariant E
 */
interface Result
{
    public function isOk(): bool;

    public function isErr(): bool;

    /**
     * @return T
     */
    public function unwrap(): mixed;

    /**
     * @return E
     */
    public function unwrapErr(): mixed;
}
