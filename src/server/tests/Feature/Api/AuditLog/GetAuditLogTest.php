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

class GetAuditLogTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use SeedsAuditLog;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査太郎'));

        $auditLogId = $this->generateUuid();
        $targetId = $this->generateUuid();

        $snapshot = [
            'title' => '描き続けた君へ',
            'tags' => ['オリジナル', '感動'],
            'meta' => ['version' => 3, 'isDisplay' => true],
        ];

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $targetId,
            $snapshot,
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $response = $this->withAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(200)
            ->json();

        $this->assertArrayHasKey('auditLog', $response);
        $this->assertSame($auditLogId, $response['auditLog']['auditLogId']);
        $this->assertSame($actorId, $response['auditLog']['adminUserId']);
        $this->assertSame('監査太郎', $response['auditLog']['adminUserName']);
        $this->assertSame('update', $response['auditLog']['action']);
        $this->assertSame('Song', $response['auditLog']['targetType']);
        $this->assertSame($targetId, $response['auditLog']['targetId']);
        $this->assertArrayHasKey('createdAt', $response['auditLog']);
        $this->assertSame('描き続けた君へ', $response['auditLog']['snapshot']['title']);
        $this->assertSame(['オリジナル', '感動'], $response['auditLog']['snapshot']['tags']);
        $this->assertSame(3, $response['auditLog']['snapshot']['meta']['version']);
        $this->assertTrue($response['auditLog']['snapshot']['meta']['isDisplay']);
    }

    #[Test]
    public function returnsForbiddenWhenLackingPermission(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege));

        $auditLogId = $this->generateUuid();

        $this->insertAuditLog(
            $auditLogId,
            $actorId,
            AuditAction::Update,
            AuditTargetType::Song,
            $this->generateUuid(),
            ['title' => '描き続けた君へ'],
            new DateTimeImmutable('2026-04-02 10:00:00'),
        );

        $this->withGeneralAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(403);
    }

    #[Test]
    public function notFound(): void
    {
        $auditLogId = $this->generateUuid();

        $this->withAuth()
            ->get(route(AuditLogRouteMap::Get, $auditLogId))
            ->assertStatus(404);
    }
}
