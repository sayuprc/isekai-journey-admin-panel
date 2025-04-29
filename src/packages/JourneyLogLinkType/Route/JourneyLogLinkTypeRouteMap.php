<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Route;

enum JourneyLogLinkTypeRouteMap: string
{
    case List = 'journey-log-link-types.index';
    case ShowCreateForm = 'journey-log-link-types.create.index';
    case Create = 'journey-log-link-types.create.handle';
    case ShowEditForm = 'journey-log-link-types.edit.index';
    case Edit = 'journey-log-link-types.edit.handle';
    case Delete = 'journey-log-link-types.delete.handle';
}
