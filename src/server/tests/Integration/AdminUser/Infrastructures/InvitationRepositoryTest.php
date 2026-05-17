<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Infrastructures;

use AdminUser\Domain\Models\Invitation\ConsumedAt;
use AdminUser\Domain\Models\Invitation\ExpiresAt;
use AdminUser\Domain\Models\Invitation\HashedToken;
use AdminUser\Domain\Models\Invitation\Invitation;
use AdminUser\Domain\Models\Invitation\InvitationId;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\InvitationRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class InvitationRepositoryTest extends DatabaseTestCase
{
    private function buildInvitation(
        string $id,
        HashedToken $hashedToken,
        Role $role = Role::General,
        array $permissions = [],
        ?DateTimeImmutable $expiresAt = null,
        ?DateTimeImmutable $consumedAt = null,
    ): Invitation {
        return new Invitation(
            InvitationId::reconstruct($id),
            $hashedToken,
            $role,
            Permissions::reconstruct($permissions),
            ExpiresAt::reconstruct($expiresAt ?? new DateTimeImmutable('2026-12-31 23:59:59')),
            is_null($consumedAt) ? null : ConsumedAt::reconstruct($consumedAt),
        );
    }

    #[Test]
    public function saveAndFindByHashedToken(): void
    {
        $repository = $this->getInstance();

        $hashedToken = HashedToken::reconstruct(str_repeat('a', 64));
        $invitation = $this->buildInvitation(
            $this->generateUuid(),
            $hashedToken,
            Role::Privilege,
            ['read_admin_user', 'write_admin_user'],
        );

        $repository->save($invitation);

        $found = $repository->findByHashedToken($hashedToken);

        $this->assertNotNull($found);
        $this->assertTrue($found->equals($invitation));
        $this->assertSame(Role::Privilege, $found->role);
        $this->assertSame(['read_admin_user', 'write_admin_user'], $found->permissions->toArray());
        $this->assertNull($found->consumedAt);
    }

    #[Test]
    public function findByHashedTokenReturnsNullWhenNotFound(): void
    {
        $repository = $this->getInstance();

        $found = $repository->findByHashedToken(HashedToken::reconstruct(str_repeat('b', 64)));

        $this->assertNull($found);
    }

    #[Test]
    public function saveUpdatesConsumedAtForExistingInvitation(): void
    {
        $repository = $this->getInstance();

        $hashedToken = HashedToken::reconstruct(str_repeat('c', 64));
        $invitation = $this->buildInvitation($this->generateUuid(), $hashedToken);

        $repository->save($invitation);

        $consumedAt = new DateTimeImmutable('2026-06-01 12:00:00');
        $consumed = $invitation->consume($consumedAt)->unwrap();

        $repository->save($consumed);

        $found = $repository->findByHashedToken($hashedToken);

        $this->assertNotNull($found);
        $this->assertTrue($found->isConsumed());
        $this->assertEquals($consumedAt, $found->consumedAt?->value);
    }

    private function getInstance(): InvitationRepository
    {
        return $this->app->make(InvitationRepository::class);
    }
}
