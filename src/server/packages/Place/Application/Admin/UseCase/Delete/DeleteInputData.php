<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $placeId)
    {
    }
}
