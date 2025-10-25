<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use OpenAPI\Client\Model\SongType as OpenApiSongType;
use SongType\Domain\Models\SongType;

class Converter
{
    public function toOpenApiSongType(SongType $songType): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setSongTypeId($songType->songTypeId->value)
            ->setSongTypeName($songType->songTypeName->value)
            ->setOrderNo($songType->orderNo->value);
    }
}
