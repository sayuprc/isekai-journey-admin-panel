<?php

declare(strict_types=1);

namespace User\Domain\Models;

use Support\Domain\ValueObjects\String\StringValueObject;

readonly class HashedPassword extends StringValueObject
{
}
