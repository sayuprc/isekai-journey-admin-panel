<?php

declare(strict_types=1);

namespace SongType\Domain\Models;

interface SongTypeFactoryInterface
{
    /**
     * @param positive-int $orderNo
     */
    public function create(string $songTypeName, int $orderNo): SongType;

    /**
     * @param positive-int $orderNo
     */
    public function reconstitute(string $songTypeId, string $songTypeName, int $orderNo): SongType;
}
