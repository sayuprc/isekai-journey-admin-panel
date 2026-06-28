<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Media\Application\Admin\Query\MediaReferencedSong;
use Media\Domain\Models\Media;
use OpenAPI\Admin\Client\Model\Media as OpenApiMedia;
use OpenAPI\Admin\Client\Model\MediaFormat as OpenApiMediaFormat;
use OpenAPI\Admin\Client\Model\MediaFormatValue;
use OpenAPI\Admin\Client\Model\MediaPlatform as OpenApiMediaPlatform;
use OpenAPI\Admin\Client\Model\MediaPlatformValue;
use OpenAPI\Admin\Client\Model\MediaReferencedSong as OpenApiMediaReferencedSong;
use OpenAPI\Admin\Client\Model\MediaType as OpenApiMediaType;
use OpenAPI\Admin\Client\Model\MediaTypeValue;

class Converter
{
    public function toOpenApiMedia(Media $media): OpenApiMedia
    {
        $openApiMedia = new OpenApiMedia()
            ->setMediaId($media->mediaId->value)
            ->setTitle($media->title->value)
            ->setUrl($media->url->value)
            ->setPublishedAt($media->publishedAt->value->toMutable())
            ->setType($this->toOpenApiMediaType($media))
            ->setFormat($this->toOpenApiMediaFormat($media))
            ->setIsDisplay($media->isDisplay)
            ->setPlatform($this->toOpenApiMediaPlatform($media));

        if (! is_null($media->thumbnail)) {
            $openApiMedia->setThumbnailUrl($media->thumbnail->value);
        }

        return $openApiMedia;
    }

    public function toOpenApiReferencedSong(MediaReferencedSong $song): OpenApiMediaReferencedSong
    {
        return new OpenApiMediaReferencedSong()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setSongOrderNo($song->songOrderNo)
            ->setMediaOrderNo($song->mediaOrderNo);
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

    private function toOpenApiMediaPlatform(Media $media): OpenApiMediaPlatform
    {
        return new OpenApiMediaPlatform()
            ->setName($media->platform->getName())
            ->setValue(MediaPlatformValue::from($media->platform->value));
    }
}
