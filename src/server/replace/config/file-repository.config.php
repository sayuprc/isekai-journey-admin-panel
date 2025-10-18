<?php

declare(strict_types=1);

use Support\DebugInfrastructures\Repository\FileRepositoryConfig;

return new FileRepositoryConfig(
    filePath: __DIR__ . '/../storage/app',
);
