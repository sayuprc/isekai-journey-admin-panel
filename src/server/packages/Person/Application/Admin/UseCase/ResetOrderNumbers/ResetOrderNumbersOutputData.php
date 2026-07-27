<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\ResetOrderNumbers;

readonly class ResetOrderNumbersOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
