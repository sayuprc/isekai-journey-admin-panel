<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Exceptions\InvalidDomainException;

abstract readonly class StringValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final protected function __construct(public string $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    /**
     * @return Result<static, DomainRuleViolationError>
     */
    public static function create(string $value): Result
    {
        if (! static::isValid($value)) {
            return new Err(new DomainRuleViolationError(static::class, static::getMessage($value)));
        }

        return new Ok(new static($value));
    }

    public static function reconstruct(string $value): static
    {
        return new static($value);
    }

    protected static function isValid(string $value): bool
    {
        return true;
    }

    protected static function getMessage(string $value): string
    {
        return "値が不正です: {$value}";
    }

    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value === $other->value;
    }
}
