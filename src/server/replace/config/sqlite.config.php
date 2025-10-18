<?php

declare(strict_types=1);

use Support\Infrastructures\Database\SQLiteConfig;

use function Tempest\env;

return new SQLiteConfig(
    path: env('DB_DATABASE'),
    foreignKeyConstraints: env('DB_FOREIGN_KEYS', true),
    busyTimeout: null,
    journalMode: null,
    synchronous: null,
);
