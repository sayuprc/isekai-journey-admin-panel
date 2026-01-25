<?php

declare(strict_types=1);

namespace Support\Domain\Validation;

final readonly class ValidationError
{
    public function __construct(public string $message)
    {
    }
}
