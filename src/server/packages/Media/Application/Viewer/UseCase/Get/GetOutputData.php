<?php

declare(strict_types=1);

namespace Media\Application\Viewer\UseCase\Get;

use Media\Application\Viewer\Query\MediaDetail;

readonly class GetOutputData
{
    public function __construct(public MediaDetail $media)
    {
    }
}
