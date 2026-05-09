<?php

declare(strict_types=1);

namespace Release\Route;

enum ReleaseRouteMap: string
{
    case Create = 'release.create';

    case Search = 'release.search';
}
