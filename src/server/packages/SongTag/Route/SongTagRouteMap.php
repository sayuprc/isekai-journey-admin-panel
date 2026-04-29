<?php

declare(strict_types=1);

namespace SongTag\Route;

enum SongTagRouteMap: string
{
    case List = 'song-tags';

    case Search = 'song-tags.search';
}
