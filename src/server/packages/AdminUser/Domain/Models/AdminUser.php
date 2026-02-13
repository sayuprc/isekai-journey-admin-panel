<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

readonly class AdminUser
{
    public function __construct(
        public AdminUserId $userId,
        public Email $email,
        public HashedPassword $hashedPassword,
    ) {
    }

    public static function reconstruct(string $userId, string $email, string $hashedPassword): self
    {
        return new self(
            AdminUserId::reconstruct($userId),
            Email::reconstruct($email),
            HashedPassword::reconstruct($hashedPassword),
        );
    }

    /**
     * @return array{user_id: string, email: string, hashed_password: string}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value,
            'email' => $this->email->value,
            'hashed_password' => $this->hashedPassword->value,
        ];
    }
}
