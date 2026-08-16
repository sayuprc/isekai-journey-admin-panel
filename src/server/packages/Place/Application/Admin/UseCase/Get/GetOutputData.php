<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Get;

use Place\Domain\Models\Place;

readonly class GetOutputData
{
    public function __construct(public Place $place)
    {
    }
}
