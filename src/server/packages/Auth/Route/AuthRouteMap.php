<?php

declare(strict_types=1);

namespace Auth\Route;

enum AuthRouteMap: string
{
    case Login = 'login';

    case Register = 'register';

    case Refresh = 'refresh';
}
