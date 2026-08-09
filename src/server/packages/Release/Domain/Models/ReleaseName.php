<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Override;
use Support\Domain\ValueObjects\String\TextValueObject;

/**
 * リリースの版名（通常盤 / 初回限定盤 など）。任意項目で、無しは null で表現する。
 */
readonly class ReleaseName extends TextValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        return $value !== '';
    }

    #[Override]
    protected static function getMessage(string $value): string
    {
        return '版名を空にすることはできません';
    }
}
