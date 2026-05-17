<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\Invitation;

use Override;
use Support\Domain\ValueObjects\String\StringValueObject;

readonly class PlainToken extends StringValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        // 32byte の hex 表現（64 文字、16 進）
        return (bool)preg_match('/\A[0-9a-f]{64}\z/', $value);
    }

    #[Override]
    protected static function getMessage(string $value): string
    {
        return '招待トークンの形式が不正です';
    }
}
