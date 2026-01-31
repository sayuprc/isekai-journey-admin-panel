<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Numeric;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\Validation\ValidationError;

abstract readonly class IntegerValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final protected function __construct(public int $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    /**
     * @return Result<static, ValidationError>
     */
    public static function create(int $value): Result
    {
        if (! static::isValid($value)) {
            return new Err(new ValidationError(static::getMessage($value)));
        }

        return new Ok(new static($value));
    }

    public static function reconstruct(int $value): static
    {
        return new static($value);
    }

    protected static function isValid(int $value): bool
    {
        return true;
    }

    protected static function getMessage(int $value): string
    {
        return "数値が不正です: {$value}";
    }
}
