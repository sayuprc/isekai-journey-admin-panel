<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;

readonly class ReleasedOn extends ImmutableDateValueObject
{
}
