<?php

declare(strict_types=1);

namespace Support\Domain\Validation;

use Closure;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;

/**
 * InputData からドメインオブジェクトを組み立てる際の検証エラー集約
 *
 * ValueObject を検証の単一情報源としたまま、全 field のエラーを
 * まとめて 1 つの DomainValidationException として報告する
 */
final class FieldErrors
{
    /** @var array<string, array<string>> */
    private array $errors = [];

    /**
     * 単一 field の組み立て。失敗したら即座に DomainValidationException を投げる
     *
     * @template T
     *
     * @param Closure(): T $build
     *
     * @return T
     */
    public static function single(string $field, Closure $build): mixed
    {
        try {
            return $build();
        } catch (DomainValidationException $e) {
            throw $e;
        } catch (InvalidDomainException $e) {
            throw new DomainValidationException([$field => [$e->getMessage()]]);
        }
    }

    /**
     * field の組み立てを試み、失敗はエラーとして集約して null を返す
     *
     * DomainValidationException は自身の field 情報を持つため、そのままマージする
     *
     * @template T
     *
     * @param Closure(): T $build
     *
     * @return T|null
     */
    public function collect(string $field, Closure $build): mixed
    {
        try {
            return $build();
        } catch (DomainValidationException $e) {
            foreach ($e->errors as $errorField => $messages) {
                $this->errors[$errorField] = [...$this->errors[$errorField] ?? [], ...$messages];
            }

            return null;
        } catch (InvalidDomainException $e) {
            $this->errors[$field][] = $e->getMessage();

            return null;
        }
    }

    /**
     * 集約したエラーがあれば DomainValidationException を投げる
     */
    public function throwIfFailed(): void
    {
        if ($this->errors !== []) {
            throw new DomainValidationException($this->errors);
        }
    }
}
