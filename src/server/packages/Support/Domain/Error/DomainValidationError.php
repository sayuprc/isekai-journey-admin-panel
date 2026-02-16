<?php

declare(strict_types=1);

namespace Support\Domain\Error;

readonly class DomainValidationError implements DomainError
{
    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(public array $errors)
    {
    }
}
