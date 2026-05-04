<?php

declare(strict_types=1);

namespace Song\Domain\Models\Persons;

enum SongPersonRole: string
{
    case Lyricist = 'lyricist';

    case Composer = 'composer';

    case Arranger = 'arranger';
}
