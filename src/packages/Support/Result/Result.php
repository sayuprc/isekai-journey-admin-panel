<?php

declare(strict_types=1);

namespace Support\Result;

/**
 * @template-covariant T
 * @template-covariant E
 */
interface Result
{
    public function isOk(): bool;

    /**
     * @return T
     */
    public function getValue(): mixed;

    /**
     * @return E
     */
    public function getErr(): mixed;
}
