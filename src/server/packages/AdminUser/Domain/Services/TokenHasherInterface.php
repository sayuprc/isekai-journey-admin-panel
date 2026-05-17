<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\Invitation\HashedToken;
use AdminUser\Domain\Models\Invitation\PlainToken;

interface TokenHasherInterface
{
    public function hash(PlainToken $plainToken): HashedToken;
}
