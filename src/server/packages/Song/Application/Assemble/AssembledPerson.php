<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

readonly class AssembledPerson
{
    public function __construct(
        public string $personId,
        public string $name,
        public string $role,
        public int $orderNo,
    ) {
    }
}
