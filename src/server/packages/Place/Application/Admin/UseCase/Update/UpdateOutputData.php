<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Update;

use Place\Domain\Models\Place;

readonly class UpdateOutputData
{
    public function __construct(public Place $place)
    {
    }
}
