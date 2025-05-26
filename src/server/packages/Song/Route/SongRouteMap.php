<?php

declare(strict_types=1);

namespace Song\Route;

enum SongRouteMap: string
{
    case List = 'songs.index';
    case ShowCreateForm = 'songs.create.index';
    case Create = 'songs.create.handle';
}
