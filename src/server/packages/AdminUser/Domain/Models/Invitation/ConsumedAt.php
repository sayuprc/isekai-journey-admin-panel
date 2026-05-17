<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\Invitation;

use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;

readonly class ConsumedAt extends ImmutableDateTimeValueObject
{
}
