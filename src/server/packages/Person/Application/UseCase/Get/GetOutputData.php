<?php

declare(strict_types=1);

namespace Person\Application\UseCase\Get;

use Person\Domain\Models\Person;

readonly class GetOutputData
{
    public function __construct(public Person $person)
    {
    }
}
