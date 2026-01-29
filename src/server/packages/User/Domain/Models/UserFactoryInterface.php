<?php

declare(strict_types=1);

namespace User\Domain\Models;

use ResultType\Result;
use Support\Domain\Validation\ValidationError;

interface UserFactoryInterface
{
    /**
     * @return Result<User, array<ValidationError>>
     */
    public function create(string $email, string $plainPassword): Result;
}
