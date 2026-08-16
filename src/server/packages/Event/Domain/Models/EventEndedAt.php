<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;

readonly class EventEndedAt extends ImmutableDateTimeValueObject
{
}
