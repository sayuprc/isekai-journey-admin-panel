<?php

declare(strict_types=1);

namespace Release\Route;

enum ReleaseRouteMap: string
{
    case Get = 'release.get';

    case Search = 'release.search';
}
