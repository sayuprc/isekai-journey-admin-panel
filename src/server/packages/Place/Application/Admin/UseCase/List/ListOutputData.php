<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\List;

use Place\Domain\Models\Place;

readonly class ListOutputData
{
    /**
     * @param array<Place> $places
     */
    public function __construct(public array $places)
    {
    }
}
