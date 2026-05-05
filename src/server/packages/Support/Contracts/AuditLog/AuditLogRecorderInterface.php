<?php

declare(strict_types=1);

namespace Support\Contracts\AuditLog;

interface AuditLogRecorderInterface
{
    /**
     * @param array<string, mixed> $snapshot
     */
    public function record(
        string $actorId,
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
        array $snapshot,
    ): void;
}
