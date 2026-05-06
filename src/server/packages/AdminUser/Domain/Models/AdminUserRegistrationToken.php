<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use DateTimeImmutable;

readonly class AdminUserRegistrationToken
{
    public function __construct(
        public AdminUserRegistrationTokenId $adminUserRegistrationTokenId,
        public AdminUserName $name,
        public Email $email,
        public Role $role,
        public RegistrationTokenHashedValue $tokenHash,
        public RegistrationTokenExpiredAt $expiredAt,
        public CreatedAt $createdAt,
        public ?DateTimeImmutable $usedAt,
    ) {
    }

    public static function reconstruct(
        string $adminUserRegistrationTokenId,
        string $name,
        string $email,
        int $role,
        string $tokenHash,
        DateTimeImmutable $expiredAt,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $usedAt,
    ): self {
        return new self(
            AdminUserRegistrationTokenId::reconstruct($adminUserRegistrationTokenId),
            AdminUserName::reconstruct($name),
            Email::reconstruct($email),
            Role::from($role),
            RegistrationTokenHashedValue::reconstruct($tokenHash),
            RegistrationTokenExpiredAt::reconstruct($expiredAt),
            CreatedAt::reconstruct($createdAt),
            $usedAt,
        );
    }

    /**
     * @return array{
     *   admin_user_registration_token_id: string,
     *   name: string,
     *   email: string,
     *   role: value-of<Role>,
     *   token_hash: string,
     *   expired_at: string,
     *   created_at: string,
     *   used_at: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'admin_user_registration_token_id' => $this->adminUserRegistrationTokenId->value,
            'name' => $this->name->value,
            'email' => $this->email->value,
            'role' => $this->role->value,
            'token_hash' => $this->tokenHash->value,
            'expired_at' => $this->expiredAt->value->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->value->format('Y-m-d H:i:s'),
            'used_at' => $this->usedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
