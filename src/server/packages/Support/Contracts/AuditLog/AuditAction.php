<?php

declare(strict_types=1);

namespace Support\Contracts\AuditLog;

enum AuditAction: string
{
    case Create = 'create';

    case Update = 'update';

    case Delete = 'delete';

    case Login = 'login';

    case Refresh = 'refresh';
}
