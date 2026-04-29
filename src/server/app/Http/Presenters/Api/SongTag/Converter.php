<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongTag;

use OpenAPI\Client\Model\SongTag as OpenApiSongTag;
use Song\Domain\Models\Tag\SongTag;

class Converter
{
    public function toOpenApiSongTag(SongTag $songTag): OpenApiSongTag
    {
        return new OpenApiSongTag()
            ->setSongTagId($songTag->songTagId->value)
            ->setName($songTag->name->value)
            ->setOrderNo($songTag->orderNo->value);
    }
}
