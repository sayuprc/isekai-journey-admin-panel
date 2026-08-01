<?php

declare(strict_types=1);

namespace Support\Domain\Exceptions;

/**
 * 複数 field にまたがるドメイン検証エラーの集約
 *
 * Fields::validate による集約を経由して ValidationFailedException に変換される
 */
class DomainValidationException extends InvalidDomainException
{
    /**
     * @param array<string, array<string>> $errors field ごとのエラーメッセージ
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('入力内容に誤りがあります');
    }
}
