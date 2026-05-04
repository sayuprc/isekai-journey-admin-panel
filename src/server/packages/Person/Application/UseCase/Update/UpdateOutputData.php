<?php

declare(strict_types=1);

namespace Person\Application\UseCase\Update;

use Person\Domain\Models\Person;

readonly class UpdateOutputData
{
    public function __construct(public Person $person)
    {
    }
}
