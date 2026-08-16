<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Create;

use Place\Domain\Models\Place;

readonly class CreateOutputData
{
    public function __construct(public Place $place)
    {
    }
}
