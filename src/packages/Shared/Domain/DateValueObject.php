<?php

declare(strict_types=1);

namespace Shared\Domain;

use DateTimeImmutable;
use DateTimeInterface;

abstract class DateValueObject
{
    public function __construct(public private(set) DateTimeInterface $value)
    {
        $this->value = new DateTimeImmutable($this->value->format('Y-m-d 00:00:00.000000'));
    }
}
