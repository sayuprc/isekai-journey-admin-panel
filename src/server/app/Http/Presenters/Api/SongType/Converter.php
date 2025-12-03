<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use SongType\Domain\Models\SongType;

class Converter
{
    public function toOpenApiSongType(SongType $songType): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($songType->getName())
            ->setValue(SongTypeValue::from($songType->value));
    }
}
