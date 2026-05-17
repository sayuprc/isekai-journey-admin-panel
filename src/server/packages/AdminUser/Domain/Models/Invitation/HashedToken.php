<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\Invitation;

use Support\Domain\ValueObjects\String\StringValueObject;

readonly class HashedToken extends StringValueObject
{
}
