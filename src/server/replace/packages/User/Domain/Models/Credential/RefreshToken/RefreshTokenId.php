<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential\RefreshToken;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class RefreshTokenId extends UuidValueObject
{
}
