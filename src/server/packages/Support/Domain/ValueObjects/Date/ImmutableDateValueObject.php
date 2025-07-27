<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Date;

use DateType\ImmutableDate;

abstract readonly class ImmutableDateValueObject
{
    public function __construct(public ImmutableDate $value)
    {
    }
}
