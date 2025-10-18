<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

readonly class SQLiteConfig
{
    public function __construct(
        public string $path,
        public ?bool $foreignKeyConstraints,
        public ?int $busyTimeout,
        public ?string $journalMode,
        public ?string $synchronous
    ) {
    }
}
