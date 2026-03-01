<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Date;

use DateType\ImmutableDate;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\Exceptions\InvalidDomainException;

abstract readonly class ImmutableDateValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final protected function __construct(public ImmutableDate $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    /**
     * @return Result<static, EntityRuleViolationError>
     */
    public static function create(ImmutableDate $value): Result
    {
        if (! static::isValid($value)) {
            return new Err(new EntityRuleViolationError(static::class, static::getMessage($value)));
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

    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value->format('Y-m-d') === $other->value->format('Y-m-d');
    }
}
