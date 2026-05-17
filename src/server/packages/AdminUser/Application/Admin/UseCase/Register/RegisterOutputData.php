<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\Register;

use AdminUser\Domain\Models\AdminUser;

readonly class RegisterOutputData
{
    public function __construct(public AdminUser $adminUser)
    {
    }
}
