<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Closure;
use Illuminate\Support\Facades\DB;
use Support\Contracts\TransactionInterface;
use Override;

readonly class DbTransaction implements TransactionInterface
{
    #[Override]
    public function scope(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
