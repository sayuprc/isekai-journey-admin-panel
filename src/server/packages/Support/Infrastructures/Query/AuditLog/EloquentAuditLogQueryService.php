<?php

declare(strict_types=1);

namespace Support\Infrastructures\Query\AuditLog;

use App\Models\AuditLog as ModelsAuditLog;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\AuditLog\Query\AuditLogDetail;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;
use Support\UseCase\AuditLog\Query\AuditLogSearchCriteria;
use Support\UseCase\AuditLog\Query\AuditLogSummary;

readonly class EloquentAuditLogQueryService implements AuditLogQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function search(AuditLogSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $this->buildQuery($criteria)
            ->orderBy('audit_logs.created_at', 'desc')
            ->orderBy('audit_logs.audit_log_id', 'desc')
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrateSummary(...))
            ->all();
    }

    #[Override]
    public function maxPage(AuditLogSearchCriteria $criteria): int
    {
        $count = $this->buildQuery($criteria)->count();

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function find(string $auditLogId): ?AuditLogDetail
    {
        $found = ModelsAuditLog::query()
            ->leftJoin('admin_users', 'audit_logs.admin_user_id', '=', 'admin_users.admin_user_id')
            ->select('audit_logs.*', 'admin_users.name as admin_user_name')
            ->where('audit_logs.audit_log_id', $this->converter->toBin($auditLogId))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return new AuditLogDetail(
            $this->converter->toUuid($found->audit_log_id),
            $this->converter->toUuid($found->admin_user_id),
            $this->stringAttribute($found, 'admin_user_name'),
            AuditAction::from($found->action),
            AuditTargetType::from($found->target_type),
            $this->converter->toUuid($found->target_id),
            $found->snapshot,
            DateTimeImmutable::createFromInterface($found->created_at),
        );
    }

    /**
     * @return Builder<ModelsAuditLog>
     */
    private function buildQuery(AuditLogSearchCriteria $criteria): Builder
    {
        $query = ModelsAuditLog::query()
            ->leftJoin('admin_users', 'audit_logs.admin_user_id', '=', 'admin_users.admin_user_id')
            ->select('audit_logs.*', 'admin_users.name as admin_user_name');

        if ($criteria->from->isPresent()) {
            $query = $query->where('audit_logs.created_at', '>=', $criteria->from->get());
        }

        if ($criteria->to->isPresent()) {
            $query = $query->where('audit_logs.created_at', '<=', $criteria->to->get());
        }

        if ($criteria->action->isPresent()) {
            $query = $query->where('audit_logs.action', $criteria->action->get()->value);
        }

        if ($criteria->targetType->isPresent()) {
            $query = $query->where('audit_logs.target_type', $criteria->targetType->get()->value);
        }

        if ($criteria->targetId->isPresent()) {
            $query = $query->where('audit_logs.target_id', $this->converter->toBin($criteria->targetId->get()));
        }

        if ($criteria->adminUserName->isPresent()) {
            $escaped = $this->escapeLike($criteria->adminUserName->get());
            $query = $query->where('admin_users.name', 'like', $escaped . '%');
        }

        return $query;
    }

    /**
     * LIKE のメタ文字 (`\`, `%`, `_`) をエスケープする。
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function stringAttribute(ModelsAuditLog $model, string $key): string
    {
        $value = $model->getAttribute($key);

        return is_string($value) ? $value : '';
    }

    private function hydrateSummary(ModelsAuditLog $model): AuditLogSummary
    {
        return new AuditLogSummary(
            $this->converter->toUuid($model->audit_log_id),
            $this->converter->toUuid($model->admin_user_id),
            $this->stringAttribute($model, 'admin_user_name'),
            AuditAction::from($model->action),
            AuditTargetType::from($model->target_type),
            $this->converter->toUuid($model->target_id),
            DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
