<?php

declare(strict_types=1);

namespace Media\Route;

enum MediaRouteMap: string
{
    case Create = 'media.create';

    case Search = 'media.search';
}
