<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Query;

use DateTimeImmutable;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Optional;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class AuditLogSearchCriteria
{
    /**
     * @param Optional<DateTimeImmutable> $from
     * @param Optional<DateTimeImmutable> $to
     * @param Optional<AuditAction>       $action
     * @param Optional<AuditTargetType>   $targetType
     * @param Optional<string>            $targetId
     * @param Optional<string>            $adminUserName
     */
    public function __construct(
        public Optional $from,
        public Optional $to,
        public Optional $action,
        public Optional $targetType,
        public Optional $targetId,
        public Optional $adminUserName,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
