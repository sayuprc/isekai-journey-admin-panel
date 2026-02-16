<?php

declare(strict_types=1);

namespace Support\UseCase\Error;

readonly class ConflictError implements UseCaseError
{
    public function __construct(
        public string $resourceName,
        public int|string $identifier,
    ) {
    }
}
