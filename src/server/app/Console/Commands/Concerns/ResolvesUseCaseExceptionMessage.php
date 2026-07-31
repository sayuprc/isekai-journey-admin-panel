<?php

declare(strict_types=1);

namespace App\Console\Commands\Concerns;

use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\DomainValidationException;
use Support\UseCase\Exceptions\UseCaseException;

/**
 * Console コマンドで既知の業務例外を運用者向けメッセージへ変換する
 */
trait ResolvesUseCaseExceptionMessage
{
    private function resolveExceptionMessage(BusinessRuleViolationException|DomainValidationException|UseCaseException $e): string
    {
        if ($e instanceof DomainValidationException) {
            foreach ($e->errors as $messages) {
                if ($messages !== []) {
                    return $messages[0];
                }
            }
        }

        return $e->getMessage();
    }
}
