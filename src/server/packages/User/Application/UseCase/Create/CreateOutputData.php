<?php

declare(strict_types=1);

namespace User\Application\UseCase\Create;

use User\Domain\Models\User;

class CreateOutputData
{
    public function __construct(public readonly User $user)
    {
    }
}
