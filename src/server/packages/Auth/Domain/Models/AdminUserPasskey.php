<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use DateTimeImmutable;

readonly class AdminUserPasskey
{
    public function __construct(
        public string $adminUserPasskeyId,
        public string $adminUserId,
        public string $credentialId,
        public string $publicKey,
        public int $signCount,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $lastUsedAt,
    ) {
    }

    public function withCounter(int $signCount, DateTimeImmutable $lastUsedAt): self
    {
        return new self(
            $this->adminUserPasskeyId,
            $this->adminUserId,
            $this->credentialId,
            $this->publicKey,
            $signCount,
            $this->createdAt,
            $lastUsedAt,
        );
    }
}
