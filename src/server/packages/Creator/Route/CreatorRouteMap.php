<?php

declare(strict_types=1);

namespace Creator\Route;

enum CreatorRouteMap: string
{
    case List = 'creators.index';
    case ShowCreateForm = 'creators.create.index';
    case Create = 'creators.create.handle';
    case ShowEditForm = 'creators.edit.index';
    case Edit = 'creators.edit.handle';
    case Delete = 'creators.delete.handle';
}
