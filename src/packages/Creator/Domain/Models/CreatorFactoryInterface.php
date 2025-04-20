<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

interface CreatorFactoryInterface
{
    public function create(string $creatorName): Creator;

    public function reconstitute(string $creatorId, string $creatorName): Creator;
}
