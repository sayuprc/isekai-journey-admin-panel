<?php

declare(strict_types=1);

namespace Auth\Route;

enum AuthRouteMap: string
{
    case RegisterStart = 'register.start';

    case RegisterFinish = 'register.finish';

    case LoginStart = 'login.start';

    case LoginFinish = 'login.finish';

    case Refresh = 'refresh';
}
