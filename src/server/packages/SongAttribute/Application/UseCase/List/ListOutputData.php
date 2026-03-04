<?php

declare(strict_types=1);

namespace SongAttribute\Application\UseCase\List;

use Song\Domain\Models\SongAttribute;

readonly class ListOutputData
{
    /**
     * @param array<SongAttribute> $songAttributes
     */
    public function __construct(public array $songAttributes)
    {
    }
}
