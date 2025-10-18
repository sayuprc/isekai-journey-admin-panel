<?php

declare(strict_types=1);

namespace SongType\Route;

enum SongTypeRouteMap: string
{
    case List = 'song-types';
    case Get = 'song-types.show';
    case Create = 'song-types.create';
    case Update = 'song-types.update';
    case Delete = 'song-types.delete';
}
