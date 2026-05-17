<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\Invitation\PlainToken;

interface PlainTokenGeneratorInterface
{
    public function generate(): PlainToken;
}
