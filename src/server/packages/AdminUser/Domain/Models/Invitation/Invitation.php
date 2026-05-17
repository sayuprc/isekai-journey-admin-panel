<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\Invitation;

use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\BusinessRuleViolationError;

readonly class Invitation
{
    public function __construct(
        public InvitationId $invitationId,
        public HashedToken $hashedToken,
        public Role $role,
        public Permissions $permissions,
        public ExpiresAt $expiresAt,
        public ?ConsumedAt $consumedAt,
    ) {
    }

    /**
     * @param list<string> $permissions
     */
    public static function reconstruct(
        string $invitationId,
        string $hashedToken,
        int $role,
        array $permissions,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $consumedAt,
    ): self {
        return new self(
            InvitationId::reconstruct($invitationId),
            HashedToken::reconstruct($hashedToken),
            Role::from($role),
            Permissions::reconstruct($permissions),
            ExpiresAt::reconstruct($expiresAt),
            is_null($consumedAt) ? null : ConsumedAt::reconstruct($consumedAt),
        );
    }

    public function isConsumed(): bool
    {
        return ! is_null($this->consumedAt);
    }

    /**
     * @return Result<self, BusinessRuleViolationError>
     */
    public function consume(DateTimeImmutable $now): Result
    {
        if ($this->isConsumed()) {
            return new Err(new BusinessRuleViolationError('この招待トークンは既に使用されています'));
        }

        if ($this->expiresAt->isExpired($now)) {
            return new Err(new BusinessRuleViolationError('この招待トークンは有効期限が切れています'));
        }

        return new Ok(new self(
            $this->invitationId,
            $this->hashedToken,
            $this->role,
            $this->permissions,
            $this->expiresAt,
            ConsumedAt::reconstruct($now),
        ));
    }

    /**
     * @return array{invitation_id: string, hashed_token: string, role: value-of<Role>, permissions: list<string>, expires_at: string, consumed_at: ?string}
     */
    public function toArray(): array
    {
        return [
            'invitation_id' => $this->invitationId->value,
            'hashed_token' => $this->hashedToken->value,
            'role' => $this->role->value,
            'permissions' => $this->permissions->toArray(),
            'expires_at' => $this->expiresAt->value->format('Y-m-d H:i:s'),
            'consumed_at' => $this->consumedAt?->value->format('Y-m-d H:i:s'),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->invitationId->equals($other->invitationId);
    }
}
