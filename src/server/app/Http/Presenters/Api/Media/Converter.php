<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Media;

use Media\Domain\Models\Media;
use Media\Domain\Models\MediaReferencedSong;
use OpenAPI\Client\Model\Media as OpenApiMedia;
use OpenAPI\Client\Model\MediaFormat as OpenApiMediaFormat;
use OpenAPI\Client\Model\MediaFormatValue;
use OpenAPI\Client\Model\MediaReferencedSong as OpenApiMediaReferencedSong;
use OpenAPI\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Client\Model\MediaTypeValue;

class Converter
{
    public function toOpenApiMedia(Media $media): OpenApiMedia
    {
        return new OpenApiMedia()
            ->setMediaId($media->mediaId->value)
            ->setTitle($media->title->value)
            ->setUrl($media->url->value)
            ->setType($this->toOpenApiMediaType($media))
            ->setFormat($this->toOpenApiMediaFormat($media))
            ->setIsDisplay($media->isDisplay);
    }

    public function toOpenApiReferencedSong(MediaReferencedSong $song): OpenApiMediaReferencedSong
    {
        return new OpenApiMediaReferencedSong()
            ->setSongId($song->songId->value)
            ->setTitle($song->title->value)
            ->setSongOrderNo($song->songOrderNo->value)
            ->setMediaOrderNo($song->mediaOrderNo->value);
    }

    private function toOpenApiMediaType(Media $media): OpenApiMediaType
    {
        return new OpenApiMediaType()
            ->setName($media->type->getName())
            ->setValue(MediaTypeValue::from($media->type->value));
    }

    private function toOpenApiMediaFormat(Media $media): OpenApiMediaFormat
    {
        return new OpenApiMediaFormat()
            ->setName($media->format->getName())
            ->setValue(MediaFormatValue::from($media->format->value));
    }
}
