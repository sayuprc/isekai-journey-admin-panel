<?php

declare(strict_types=1);

namespace Creator\Infrastructures;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Support\Contracts\UuidGeneratorInterface;

readonly class CreatorFactory implements CreatorFactoryInterface
{
    public function __construct(private UuidGeneratorInterface $uuid)
    {
    }

    public function create(string $creatorName): Creator
    {
        return new Creator(
            new CreatorId($this->uuid->generate()),
            new CreatorName($creatorName),
        );
    }

    public function reconstitute(string $creatorId, string $creatorName): Creator
    {
        return new Creator(
            new CreatorId($creatorId),
            new CreatorName($creatorName),
        );
    }
}
