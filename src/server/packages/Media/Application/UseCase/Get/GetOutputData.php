<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Get;

use Media\Domain\Models\Media;

readonly class GetOutputData
{
    public function __construct(public Media $media)
    {
    }
}
