<?php

declare(strict_types=1);

namespace Support\UseCase\Error;

readonly class InvalidInputError implements UseCaseError
{
    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(public array $errors)
    {
    }
}
