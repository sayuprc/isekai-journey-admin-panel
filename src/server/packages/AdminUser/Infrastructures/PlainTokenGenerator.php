<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\Invitation\PlainToken;
use AdminUser\Domain\Services\PlainTokenGeneratorInterface;
use Override;

readonly class PlainTokenGenerator implements PlainTokenGeneratorInterface
{
    private const int BYTES = 32;

    #[Override]
    public function generate(): PlainToken
    {
        return PlainToken::reconstruct(bin2hex(random_bytes(self::BYTES)));
    }
}
