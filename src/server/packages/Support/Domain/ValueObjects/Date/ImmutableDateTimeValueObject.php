<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Date;

use DateTimeImmutable;

abstract class ImmutableDateTimeValueObject
{
    public function __construct(public readonly DateTimeImmutable $value)
    {
    }
}
