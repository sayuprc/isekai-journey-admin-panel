<?php

declare(strict_types=1);

namespace Song\Route;

enum SongTagRouteMap: string
{
    case List = 'song-tags';

    case Create = 'song-tags.create';
}
