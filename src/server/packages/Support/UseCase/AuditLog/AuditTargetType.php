<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog;

enum AuditTargetType: string
{
    case AdminUser = 'AdminUser';

    case Person = 'Person';

    case Song = 'Song';

    case SongTag = 'SongTag';
}
