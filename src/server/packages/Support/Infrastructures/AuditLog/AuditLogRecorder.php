<?php

declare(strict_types=1);

namespace Support\Infrastructures\AuditLog;

use AdminUser\Domain\Models\AdminUserId;
use App\Models\AuditLog as ModelsAuditLog;
use Auth\Domain\Models\AuthContext;
use LogicException;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\ValueObjects\String\UuidValueObject;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class AuditLogRecorder implements AuditLogRecorderInterface
{
    public function __construct(
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuidGenerator,
        private UuidConverterInterface $uuidConverter,
        private AuthContext $authContext,
    ) {
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    #[Override]
    public function record(
        AuditAction $action,
        AuditTargetType $targetType,
        UuidValueObject $targetId,
        array $snapshot,
        ?AdminUserId $actorId = null,
    ): void {
        $resolvedActor = $actorId ?? $this->authContext->get()?->adminUserId;

        if (is_null($resolvedActor)) {
            throw new LogicException('audit log の actor が解決できません');
        }

        ModelsAuditLog::create([
            'audit_log_id' => $this->uuidConverter->toBin($this->uuidGenerator->generate()),
            'admin_user_id' => $this->uuidConverter->toBin($resolvedActor->value),
            'action' => $action->value,
            'target_type' => $targetType->value,
            'target_id' => $this->uuidConverter->toBin($targetId->value),
            'snapshot' => $snapshot,
            'created_at' => $this->clock->now(),
        ]);
    }
}
