<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\Invitation\HashedToken;
use AdminUser\Domain\Models\Invitation\PlainToken;
use AdminUser\Domain\Services\TokenHasherInterface;
use Override;

readonly class TokenHasher implements TokenHasherInterface
{
    public function __construct(private string $secret)
    {
    }

    #[Override]
    public function hash(PlainToken $plainToken): HashedToken
    {
        return HashedToken::reconstruct(hash_hmac('sha256', $plainToken->value, $this->secret));
    }
}
