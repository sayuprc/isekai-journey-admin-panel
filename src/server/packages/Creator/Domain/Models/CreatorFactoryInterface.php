<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

interface CreatorFactoryInterface
{
    public function create(CreatorId $creatorId, CreatorName $creatorName): Creator;
}
