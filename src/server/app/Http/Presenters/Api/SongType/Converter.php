<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use OpenAPI\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Client\Model\SongTypeValue;
use Song\Domain\Models\SongType;

class Converter
{
    public function toOpenApiSongType(SongType $type): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($type->getName())
            ->setValue(SongTypeValue::from($type->value));
    }
}
