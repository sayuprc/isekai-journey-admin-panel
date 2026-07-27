<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\ResetOrderNumbers;

readonly class ResetOrderNumbersOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
