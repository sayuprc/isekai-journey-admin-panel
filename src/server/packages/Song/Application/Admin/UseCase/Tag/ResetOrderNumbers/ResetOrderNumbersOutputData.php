<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\ResetOrderNumbers;

readonly class ResetOrderNumbersOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
