<?php

declare(strict_types=1);

namespace Performer\Route;

enum PerformerRouteMap: string
{
    case List = 'performers';

    case Search = 'performers.search';

    case Get = 'performers.show';

    case Create = 'performers.create';

    case Update = 'performers.update';

    case Delete = 'performers.delete';
}
