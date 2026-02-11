<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use Override;
use Support\Domain\ValueObjects\String\StringValueObject;

readonly class PlainPassword extends StringValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        // TODO 暫定
        return 0 < mb_strlen($value);
    }
}
