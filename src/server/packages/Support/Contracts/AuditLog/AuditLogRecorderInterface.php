<?php

declare(strict_types=1);

namespace Support\Contracts\AuditLog;

interface AuditLogRecorderInterface
{
    /**
     * @param array<string, mixed> $snapshot
     */
    public function record(
        AuditAction $action,
        AuditTargetType $targetType,
        string $targetId,
        array $snapshot,
        ?string $actorId = null,
    ): void;
}
