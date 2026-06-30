<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;

/**
 * 人間が読むテキストを表す値オブジェクトの基底。
 *
 * 生成・再構築の入口で値を Unicode NFC に正規化し、「title は常に NFC」という不変条件を保証する。
 * JWT やハッシュ値のようにバイト完全一致が要る値は {@see StringValueObject} を直接継承すること。
 */
abstract readonly class TextValueObject extends StringValueObject
{
    /**
     * @return Result<static, EntityRuleViolationError>
     */
    public static function create(string $value): Result
    {
        return parent::create(TextNormalizer::toNfc($value));
    }

    public static function reconstruct(string $value): static
    {
        return parent::reconstruct(TextNormalizer::toNfc($value));
    }
}
