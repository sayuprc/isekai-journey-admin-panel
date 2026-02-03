<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

class SQLiteConfig
{
    public function __construct(
        public readonly string $database,
        public readonly ?bool $foreignKeyConstraints,
        public readonly ?int $busyTimeout,
        public readonly ?string $journalMode,
        public readonly ?string $synchronous,
    ) {
    }
}
