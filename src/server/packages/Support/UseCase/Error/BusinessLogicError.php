<?php

declare(strict_types=1);

namespace Support\UseCase\Error;

readonly class BusinessLogicError implements UseCaseError
{
    public function __construct(public string $message)
    {
    }
}
