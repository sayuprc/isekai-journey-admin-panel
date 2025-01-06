<?php

declare(strict_types=1);

namespace Shared\Domain;

use DateTimeInterface;

abstract class DateTimeValueObject
{
    public function __construct(public readonly DateTimeInterface $value)
    {
    }
}
