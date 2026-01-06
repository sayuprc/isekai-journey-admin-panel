<?php

declare(strict_types=1);

namespace JourneyLog\Route;

enum JourneyLogRouteMap: string
{
    case List = 'journey-logs.index';

    case Create = 'journey-logs.create';

    case Edit = 'journey-logs.edit';

    case Delete = 'journey-logs.delete';
}
