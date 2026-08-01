<?php

declare(strict_types=1);

namespace Support\Domain\Validation;

use Closure;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;

/**
 * 単一 field のドメインオブジェクト構築の試行結果
 *
 * of で即時に構築を試みて成否を保持する。エラーの集約と報告は Fields::validate が担う
 *
 * @template-covariant T
 */
final class Field
{
    /**
     * 構築済みの値を返す closure。失敗した Field では元の例外を再 throw する
     *
     * @param Closure(): T                 $value
     * @param array<string, array<string>> $errors
     */
    private function __construct(
        private readonly Closure $value,
        public readonly array $errors,
    ) {
    }

    /**
     * field の構築を即時に試み、結果を保持した Field を返す
     *
     * DomainValidationException は自身の field 情報を持つため、そのまま保持する
     *
     * @template TValue
     *
     * @param Closure(): TValue $build
     *
     * @return self<TValue>
     */
    public static function of(string $field, Closure $build): self
    {
        try {
            $value = $build();

            return new self(static fn (): mixed => $value, []);
        } catch (DomainValidationException $e) {
            return new self(static fn (): never => throw $e, $e->errors);
        } catch (InvalidDomainException $e) {
            return new self(static fn (): never => throw $e, [$field => [$e->getMessage()]]);
        }
    }

    /**
     * 自身のエラーがあれば DomainValidationException を投げ、なければ構築済みの値を返す
     *
     * 単一 field の検証はこれで完結する。複数 field をまとめて報告する場合は
     * Fields::validate で集約してから value() で取り出す
     *
     * @return T
     */
    public function validate(): mixed
    {
        if ($this->errors !== []) {
            throw new DomainValidationException($this->errors);
        }

        return ($this->value)();
    }

    /**
     * 構築済みの値を返す。構築に失敗していた場合は元の例外を再 throw する
     *
     * Fields::validate の通過後は失敗した Field に到達しないことが保証される
     *
     * @return T
     */
    public function value(): mixed
    {
        return ($this->value)();
    }
}
