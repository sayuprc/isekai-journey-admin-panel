<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\List;

use Performer\Domain\Models\Performer;

readonly class ListOutputData
{
    /**
     * @param array<Performer> $performers
     */
    public function __construct(public array $performers)
    {
    }
}
