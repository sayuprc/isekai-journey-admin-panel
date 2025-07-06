<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures;

use Closure;
use Support\Contracts\TransactionInterface;

class NopTransaction implements TransactionInterface
{
    public function scope(Closure $callback): mixed
    {
        return $callback();
    }
}
