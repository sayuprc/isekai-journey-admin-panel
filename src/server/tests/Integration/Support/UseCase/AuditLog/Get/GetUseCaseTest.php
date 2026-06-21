<?php

declare(strict_types=1);

namespace Tests\Integration\Support\UseCase\AuditLog\Get;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use App\Models\AuditLog as ModelsAuditLog;
use Auth\Domain\Models\AuthContext;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\AuditLog\Get\GetInputData;
use Support\UseCase\AuditLog\Get\GetUseCase;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\NotFoundError;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function returnsAuditLogDetail(): void
    {
        $actorId = $this->generateUuid();
        $this->storeAdminUsers($this->createAdminUser($actorId, 'actor@example.com', Role::Privilege, name: '監査テストユーザーA'));

        $auditLogId = $this->generateUuid();
        $targetId = $this->generateUuid();
        $snapshot = ['title' => 'テスト楽曲', 'order_no' => 1];

        $this->insertAuditLog($auditLogId, $actorId, AuditAction::Update, AuditTargetType::Song, $targetId, $snapshot);

        $result = $this->getInstance()->handle(new GetInputData($auditLogId));

        $this->assertTrue($result->isOk());

        $detail = $result->unwrap()->auditLog;

        $this->assertSame($auditLogId, $detail->auditLogId);
        $this->assertSame($actorId, $detail->adminUserId);
        $this->assertSame('監査テストユーザーA', $detail->adminUserName);
        $this->assertSame(AuditAction::Update, $detail->action);
        $this->assertSame(AuditTargetType::Song, $detail->targetType);
        $this->assertSame($targetId, $detail->targetId);
        $this->assertSame($snapshot, $detail->snapshot);
    }

    #[Test]
    public function returnsNotFoundWhenAuditLogDoesNotExist(): void
    {
        $auditLogId = $this->generateUuid();

        $result = $this->getInstance()->handle(new GetInputData($auditLogId));

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(NotFoundError::class, $error);
        $this->assertSame('監査ログ', $error->resourceName);
        $this->assertSame($auditLogId, $error->identifier);
    }

    #[Test]
    public function returnsForbiddenWhenLackingPermission(): void
    {
        $context = $this->app->make(AuthContext::class);

        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            '一般ユーザー',
            'general@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $useCase = $this->app->make(GetUseCase::class);

        $result = $useCase->handle(new GetInputData($this->generateUuid()));

        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AuthorizationError::class, $result->unwrapErr());
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function insertAuditLog(
        string $auditLogId,
        string $actorId,
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
        array $snapshot,
    ): void {
        $converter = $this->app->make(UuidConverterInterface::class);

        ModelsAuditLog::query()->insert([
            'audit_log_id' => $converter->toBin($auditLogId),
            'admin_user_id' => $converter->toBin($actorId),
            'action' => $action->value,
            'target_type' => $targetType->value,
            'target_id' => $converter->toBin($targetId),
            'snapshot' => json_encode($snapshot),
            'created_at' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
        ]);
    }
}
