<?php

declare(strict_types=1);

namespace Auth\Domain\Models\AdminRegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use DateTimeInterface;

readonly class AdminRegistrationToken
{
    public function __construct(
        public AdminRegistrationTokenId $adminRegistrationTokenId,
        public Email $email,
        public Role $role,
        public HashedTokenValue $token,
        private ExpiredAt $expiredAt,
        private ConsumptionStatus $status,
    ) {
    }

    public static function reconstruct(
        string $adminRegistrationTokenId,
        string $email,
        int $role,
        string $token,
        DateTimeImmutable $expiredAt,
        int $status,
    ): self {
        return new self(
            AdminRegistrationTokenId::reconstruct($adminRegistrationTokenId),
            Email::reconstruct($email),
            Role::from($role),
            HashedTokenValue::reconstruct($token),
            ExpiredAt::reconstruct($expiredAt),
            ConsumptionStatus::from($status),
        );
    }

    /**
     * @return array{admin_registration_token_id: string, email: string, role: value-of<Role>, token: string, expired_at: non-falsy-string, status: value-of<ConsumptionStatus>}
     */
    public function toArray(): array
    {
        return [
            'admin_registration_token_id' => $this->adminRegistrationTokenId->value,
            'email' => $this->email->value,
            'role' => $this->role->value,
            'token' => $this->token->value,
            'expired_at' => $this->expiredAt->value->format('Y-m-d H:i:s'),
            'status' => $this->status->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->adminRegistrationTokenId->equals($other->adminRegistrationTokenId);
    }

    public function isAvailable(DateTimeInterface $now): bool
    {
        return $this->status->isAvailable() && ! $this->expiredAt->isExpired($now);
    }

    public function consume(): self
    {
        return new self(
            $this->adminRegistrationTokenId,
            $this->email,
            $this->role,
            $this->token,
            $this->expiredAt,
            ConsumptionStatus::Consumed,
        );
    }
}
