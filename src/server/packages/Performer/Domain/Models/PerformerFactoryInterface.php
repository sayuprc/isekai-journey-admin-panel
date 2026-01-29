<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

use ResultType\Result;
use Support\Domain\Validation\ValidationError;

interface PerformerFactoryInterface
{
    /**
     * @return Result<Performer, array<ValidationError>>
     */
    public function create(string $performerName, int $orderNo): Result;

    /**
     * @return Result<Performer, array<ValidationError>>
     */
    public function reconstitute(string $performerId, string $performerName, int $orderNo): Result;
}
