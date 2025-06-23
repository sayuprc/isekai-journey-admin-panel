<?php

declare(strict_types=1);

namespace SongType\Route;

enum SongTypeRouteMap: string
{
    case List = 'song-types';
    case Get = 'song-types.show';
    case Create = 'song-types.create';
    case ShowEditForm = 'song-types.edit.index';
    case Edit = 'song-types.edit.handle';
    case Delete = 'song-types.delete.handle';
}
