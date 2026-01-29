<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

use ResultType\Result;
use Support\Domain\Validation\ValidationError;

interface CreatorFactoryInterface
{
    /**
     * @return Result<Creator, array<ValidationError>>
     */
    public function create(string $creatorName): Result;

    /**
     * @return Result<Creator, array<ValidationError>>
     */
    public function reconstitute(string $creatorId, string $creatorName): Result;
}
