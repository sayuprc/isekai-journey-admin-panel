<?php

declare(strict_types=1);

namespace Performer\Route;

enum PerformerRouteMap: string
{
    case List = 'performers';
    case Create = 'performers.create';
}
