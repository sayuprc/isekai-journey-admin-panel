<?php

declare(strict_types=1);

namespace Auth\Route;

enum AuthRouteMap: string
{
    case Login = 'login';

    case Refresh = 'refresh';

    case Register = 'register';
}
