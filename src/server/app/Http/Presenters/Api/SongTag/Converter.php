<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongTag;

use OpenAPI\Client\Model\SongTag as OpenApiSongTag;
use Song\Domain\Models\Tag;

class Converter
{
    public function toOpenApiSongTag(Tag $tag): OpenApiSongTag
    {
        return new OpenApiSongTag()
            ->setSongTagId($tag->tagId->value)
            ->setName($tag->name->value)
            ->setOrderNo($tag->orderNo->value);
    }
}
