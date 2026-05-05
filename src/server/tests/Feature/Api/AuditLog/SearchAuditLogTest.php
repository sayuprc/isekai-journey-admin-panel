<?php

declare(strict_types=1);

namespace Tests\Feature\Api\AuditLog;

use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\Route\AuditLogRouteMap;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchAuditLogTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use SeedsAuditLog;
    use WithAuth;

    #[Test]
    public function returnsListWithPermission(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査太郎'));

        $auditLogId = $this->generateUuid();
        $targetId = $this->generateUuid();

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $targetId,
            ['title' => '描き続けた君へ'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Search))
            ->assertStatus(200)
            ->json();

        $this->assertSame(1, $response['maxPage']);
        $this->assertCount(1, $response['auditLogs']);
        $this->assertSame($auditLogId, $response['auditLogs'][0]['auditLogId']);
        $this->assertSame($actorId, $response['auditLogs'][0]['adminUserId']);
        $this->assertSame('監査太郎', $response['auditLogs'][0]['adminUserName']);
        $this->assertSame('update', $response['auditLogs'][0]['action']);
        $this->assertSame('Song', $response['auditLogs'][0]['targetType']);
        $this->assertSame($targetId, $response['auditLogs'][0]['targetId']);
    }

    #[Test]
    public function filtersByAdminUserNameWithPartialMatch(): void
    {
        $actor1Id = $this->generateUuid();
        $actor2Id = $this->generateUuid();
        $this->storeAdminUsers(
            $this->createAdminUser($actor1Id, 'actor1@example.com', Role::Privilege, name: '監査太郎'),
            $this->createAdminUser($actor2Id, 'actor2@example.com', Role::Privilege, name: '別人花子'),
        );

        $this->insertAuditLog(
            $this->generateUuid(),
            $actor1Id,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => 'A'],
            new DateTimeImmutable('2026-04-01 10:00:00'),
        );
        $this->insertAuditLog(
            $this->generateUuid(),
            $actor2Id,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => 'B'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Search, ['admin_user_name' => '監査']))
            ->assertStatus(200)
            ->json();

        $this->assertCount(1, $response['auditLogs']);
        $this->assertSame('監査太郎', $response['auditLogs'][0]['adminUserName']);
    }

    #[Test]
    public function returnsForbiddenWhenLackingPermission(): void
    {
        $this->withGeneralAuth()
            ->get(route(AuditLogRouteMap::Search))
            ->assertStatus(403);
    }
}
