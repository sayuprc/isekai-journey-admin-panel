<?php

declare(strict_types=1);

namespace Support\Domain\Error;

readonly class BusinessRuleViolationError implements DomainError
{
    public function __construct(public string $message)
    {
    }
}
