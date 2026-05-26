<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Infrastructures\AdminUserPasskeyRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class AdminUserPasskeyRepositoryTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function saveAndFindAndUpdate(): void
    {
        $adminUserId = $this->generateUuid();
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($adminUserId, 'user@example.com', Role::General),
        );

        $repository = $this->app->make(AdminUserPasskeyRepository::class);
        $passkey = new AdminUserPasskey(
            $this->generateUuid(),
            $adminUserId,
            'user-handle',
            'Primary passkey',
            'credential-id',
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal', 'hybrid'],
            true,
            false,
            1,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        );

        $repository->save($passkey);

        $this->assertEquals($passkey, $repository->findByCredentialId('credential-id'));
        $this->assertEquals($passkey, $repository->findByUserHandle('user-handle'));
        $this->assertEquals([$passkey], $repository->findByAdminUserId($adminUserId));

        $updated = $passkey->withCounter(2, new DateTimeImmutable('2026-01-02 00:00:00'));
        $repository->update($updated);

        $found = $repository->findByCredentialId('credential-id');
        $this->assertNotNull($found);
        $this->assertSame(2, $found->signCount);
        $this->assertEquals(new DateTimeImmutable('2026-01-02 00:00:00'), $found->lastUsedAt);
    }
}
