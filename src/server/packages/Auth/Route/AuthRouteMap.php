<?php

declare(strict_types=1);

namespace Auth\Route;

enum AuthRouteMap: string
{
    case Login = 'login';

    case Refresh = 'refresh';

    case RegisterStart = 'register.start';

    case RegisterFinish = 'register.finish';
}
