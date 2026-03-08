<?php

declare(strict_types=1);

namespace Creator\Route;

enum CreatorRouteMap: string
{
    case List = 'creators';

    case Search = 'creators.search';

    case Get = 'creators.show';

    case Create = 'creators.create';

    case Update = 'creators.update';

    case Delete = 'creators.delete';
}
