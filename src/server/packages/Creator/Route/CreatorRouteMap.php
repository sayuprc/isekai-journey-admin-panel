<?php

declare(strict_types=1);

namespace Creator\Route;

enum CreatorRouteMap: string
{
    case List = 'creators';
    case Get = 'creators.show';
    case Create = 'creators.create';
    case ShowEditForm = 'creators.edit.index';
    case Edit = 'creators.edit.handle';
    case Delete = 'creators.delete.handle';
}
