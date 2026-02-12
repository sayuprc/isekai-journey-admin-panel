<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Date;

use DateType\ImmutableDate;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\Validation\ValidationError;

abstract readonly class ImmutableDateValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final public function __construct(public ImmutableDate $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    /**
     * @return Result<static, ValidationError>
     */
    public static function create(ImmutableDate $value): Result
    {
        if (! static::isValid($value)) {
            return new Err(new ValidationError(static::getMessage($value)));
        }

        return new Ok(new static($value));
    }

    public static function reconstruct(ImmutableDate $value): static
    {
        return new static($value);
    }

    protected static function isValid(ImmutableDate $value): bool
    {
        return true;
    }

    protected static function getMessage(ImmutableDate $value): string
    {
        return "日付が不正です: {$value->format('Y-m-d')}";
    }
}
