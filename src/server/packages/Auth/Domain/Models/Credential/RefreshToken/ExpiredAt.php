<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use DateTimeInterface;
use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;

readonly class ExpiredAt extends ImmutableDateTimeValueObject
{
    public function isPast(DateTimeInterface $now): bool
    {
        return $this->value <= $now;
    }
}
