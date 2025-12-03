<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\RefreshToken;

use Support\Domain\ValueObjects\Boolean\BooleanValueObject;

readonly class IsUsed extends BooleanValueObject
{
}
