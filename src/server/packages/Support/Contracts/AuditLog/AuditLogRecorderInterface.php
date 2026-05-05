<?php

declare(strict_types=1);

namespace Support\Contracts\AuditLog;

interface AuditLogRecorderInterface
{
    /**
     * $snapshot は原則として Entity の `toArray()` をそのまま渡す。
     * パスワード等の機微情報を含む集約のときだけ、機微値を除外した配列を手組みで渡すこと。
     *
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
