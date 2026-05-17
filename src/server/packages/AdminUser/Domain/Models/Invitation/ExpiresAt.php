<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\Invitation;

use DateTimeInterface;
use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;

readonly class ExpiresAt extends ImmutableDateTimeValueObject
{
    public function isExpired(DateTimeInterface $now): bool
    {
        return $this->value <= $now;
    }
}
