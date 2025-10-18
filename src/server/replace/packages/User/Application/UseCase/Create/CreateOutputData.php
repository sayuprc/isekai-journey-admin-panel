<?php

declare(strict_types=1);

namespace User\Application\UseCase\Create;

use User\Domain\Models\User;

readonly class CreateOutputData
{
    public function __construct(public User $user)
    {
    }
}
