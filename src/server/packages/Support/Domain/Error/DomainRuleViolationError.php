<?php

declare(strict_types=1);

namespace Support\Domain\Error;

readonly class DomainRuleViolationError implements DomainError
{
    public function __construct(
        public string $field,
        public string $message,
    ) {
    }
}
