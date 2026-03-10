<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures;

use Closure;
use Support\Contracts\TransactionInterface;
use Override;

readonly class NopTransaction implements TransactionInterface
{
    #[Override]
    public function scope(Closure $callback): mixed
    {
        return $callback();
    }
}
