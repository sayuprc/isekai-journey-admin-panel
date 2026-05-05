<?php

declare(strict_types=1);

namespace Support\Infrastructures\AuditLog;

use App\Models\AuditLog as ModelsAuditLog;
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
    ) {
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    #[Override]
    public function record(
        string $actorId,
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
        array $snapshot,
    ): void {
        ModelsAuditLog::create([
            'audit_log_id' => $this->uuidConverter->toBin($this->uuidGenerator->generate()),
            'admin_user_id' => $this->uuidConverter->toBin($actorId),
            'action' => $action->value,
            'target_type' => $targetType->value,
            'target_id' => $this->uuidConverter->toBin($targetId),
            'snapshot' => $snapshot,
            'created_at' => $this->clock->now(),
        ]);
    }
}
