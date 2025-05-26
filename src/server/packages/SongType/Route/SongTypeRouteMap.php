<?php

declare(strict_types=1);

namespace SongType\Route;

enum SongTypeRouteMap: string
{
    case List = 'song-types.index';
    case ShowCreateForm = 'song-types.create.index';
    case Create = 'song-types.create.handle';
    case ShowEditForm = 'song-types.edit.index';
    case Edit = 'song-types.edit.handle';
    case Delete = 'song-types.delete.handle';
}
