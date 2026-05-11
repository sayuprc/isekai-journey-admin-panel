<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\Get;

use Song\Application\Viewer\Query\SongDetail;

readonly class GetOutputData
{
    public function __construct(public SongDetail $song)
    {
    }
}
