<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Route;

enum JourneyLogLinkTypeRouteMap: string
{
    case List = 'journey-log-link-types';

    case Create = 'journey-log-link-types.create';

    case Edit = 'journey-log-link-types.edit';

    case Delete = 'journey-log-link-types.delete';
}
