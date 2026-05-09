<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Get;

use Release\Domain\Models\Release;

readonly class GetOutputData
{
    public function __construct(public Release $release)
    {
    }
}
