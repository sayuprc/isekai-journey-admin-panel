<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongAttribute;

use OpenAPI\Client\Model\SongAttribute as OpenApiSongAttribute;
use OpenAPI\Client\Model\SongAttributeValue;
use Song\Domain\Models\SongAttribute;

class Converter
{
    public function toOpenApiSongAttribute(SongAttribute $attribute): OpenApiSongAttribute
    {
        return new OpenApiSongAttribute()
            ->setName($attribute->getName())
            ->setValue(SongAttributeValue::from($attribute->value));
    }
}
