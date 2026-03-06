<?php

declare(strict_types=1);

namespace Song\Application\UseCase\ListAttribute;

use Song\Domain\Models\SongAttribute;

readonly class ListAttributeOutputData
{
    /**
     * @param array<SongAttribute> $attributes
     */
    public function __construct(public array $attributes)
    {
    }
}
