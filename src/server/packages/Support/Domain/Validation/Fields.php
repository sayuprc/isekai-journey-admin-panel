<?php

declare(strict_types=1);

namespace Support\Domain\Validation;

use Support\Domain\Exceptions\DomainValidationException;

/**
 * InputData からドメインオブジェクトを組み立てる際の検証エラー集約
 *
 * ValueObject を検証の単一情報源としたまま、Field が保持する全 field のエラーを
 * まとめて 1 つの DomainValidationException として報告する。通過後は各 Field の
 * value から構築済みの値をそのまま取り出せる
 */
final class Fields
{
    /**
     * @param Field<mixed> ...$fields
     */
    public static function validate(Field ...$fields): void
    {
        $errors = [];

        foreach ($fields as $field) {
            foreach ($field->errors as $errorField => $messages) {
                $errors[$errorField] = [...$errors[$errorField] ?? [], ...$messages];
            }
        }

        if ($errors !== []) {
            throw new DomainValidationException($errors);
        }
    }
}
