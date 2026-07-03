<?php

declare(strict_types=1);

namespace App\Console\Commands\Concerns;

use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

trait ResolvesUseCaseErrorMessage
{
    private function resolveErrorMessage(UseCaseError $error): string
    {
        if ($error instanceof BusinessLogicError) {
            return $error->message;
        }

        if (! $error instanceof InvalidInputError) {
            return '';
        }

        foreach ($error->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return '';
    }
}
