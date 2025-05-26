<?php

declare(strict_types=1);

namespace Song\Domain\Models\Archives;

enum ArchiveType: int
{
    case YouTube = 1;
    case Twitter = 2;
    case NonLink = 3;
}
