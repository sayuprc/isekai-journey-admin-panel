<?php

declare(strict_types=1);

namespace Place\Route;

enum PlaceRouteMap: string
{
    case Create = 'places.create';

    case List = 'places.list';

    case Search = 'places.search';

    case Get = 'places.get';

    case Update = 'places.update';

    case Delete = 'places.delete';
}
