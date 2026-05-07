<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\Create;

use AdminUser\Domain\Models\AdminUser;

readonly class CreateOutputData
{
    public function __construct(public AdminUser $adminUser)
    {
    }
}
