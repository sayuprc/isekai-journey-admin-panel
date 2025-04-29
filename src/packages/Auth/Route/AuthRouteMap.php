<?php

declare(strict_types=1);

namespace Auth\Route;

enum AuthRouteMap: string
{
    case ShowLoginForm = 'login';
    case Login = 'login.handle';
}
