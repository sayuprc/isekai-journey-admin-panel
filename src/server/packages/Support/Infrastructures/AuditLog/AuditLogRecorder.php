<?php

declare(strict_types=1);

namespace Support\Infrastructures\AuditLog;

use App\Models\AuditLog as ModelsAuditLog;
use Auth\Domain\Models\AuthContext;
use LogicException;
use Override;
use Support\Contracts\AuditLog\AuditAction;
use Support\Contracts\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\AuditLog\AuditTargetType;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;

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
        string $targetId,
        array $snapshot,
        ?string $actorId = null,
    ): void {
        $resolvedActorId = $actorId ?? $this->authContext->get()?->adminUserId->value;

        if (is_null($resolvedActorId)) {
            throw new LogicException('audit log の actor が解決できません');
        }

        ModelsAuditLog::create([
            'audit_log_id' => $this->uuidConverter->toBin($this->uuidGenerator->generate()),
            'admin_user_id' => $this->uuidConverter->toBin($resolvedActorId),
            'action' => $action->value,
            'target_type' => $targetType->value,
            'target_id' => $this->uuidConverter->toBin($targetId),
            'snapshot' => $snapshot,
            'created_at' => $this->clock->now(),
        ]);
    }
}
