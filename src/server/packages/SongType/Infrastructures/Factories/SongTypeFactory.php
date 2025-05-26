<?php

declare(strict_types=1);

namespace SongType\Infrastructures\Factories;

use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeFactoryInterface;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use Support\Domain\ValueObjects\OrderNo;
use Support\Uuid\UuidGeneratorInterface;

class SongTypeFactory implements SongTypeFactoryInterface
{
    public function __construct(private readonly UuidGeneratorInterface $uuid)
    {
    }

    /**
     * @param positive-int $orderNo
     */
    public function create(string $songTypeName, int $orderNo): SongType
    {
        return new SongType(
            new SongTypeId($this->uuid->generate()),
            new SongTypeName($songTypeName),
            new OrderNo($orderNo),
        );
    }

    /**
     * @param positive-int $orderNo
     */
    public function reconstitute(string $songTypeId, string $songTypeName, int $orderNo): SongType
    {
        return new SongType(
            new SongTypeId($songTypeId),
            new SongTypeName($songTypeName),
            new OrderNo($orderNo),
        );
    }
}
