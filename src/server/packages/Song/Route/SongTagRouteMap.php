<?php

declare(strict_types=1);

namespace Song\Route;

enum SongTagRouteMap: string
{
    case List = 'song-tags';

    case Create = 'song-tags.create';

    case Update = 'song-tags.update';

    case Delete = 'song-tags.delete';

    case Search = 'song-tags.search';

    case Get = 'song-tags.get';
}
