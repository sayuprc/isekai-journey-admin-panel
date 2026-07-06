<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use OpenAPI\Admin\Client\Model\Medium as OpenApiMedium;
use OpenAPI\Admin\Client\Model\MediumFormatValue;
use OpenAPI\Admin\Client\Model\Release as OpenApiRelease;
use OpenAPI\Admin\Client\Model\ReleaseReferencedSong as OpenApiReleaseReferencedSong;
use OpenAPI\Admin\Client\Model\Track as OpenApiTrack;
use Release\Application\Admin\Query\ReleaseReferencedSong;
use Release\Domain\Models\Medium;
use Release\Domain\Models\Release;
use Release\Domain\Models\Track;

class Converter
{
    public function toOpenApiRelease(Release $release): OpenApiRelease
    {
        return new OpenApiRelease([
            'jacket_art_url' => $release->jacketArtUrl?->value,
        ])
            ->setReleaseId($release->releaseId->value)
            ->setReleaseGroupId($release->releaseGroupId->value)
            ->setName($release->name->value)
            ->setReleasedOn($release->releasedOn->value->toMutable())
            ->setDescription($release->description->value)
            ->setIsDisplay($release->isDisplay)
            ->setOrderNo($release->orderNo->value)
            ->setMedia($release->media->toGeneric()->map($this->toOpenApiMedium(...))->toArray());
    }

    public function toOpenApiMedium(Medium $medium): OpenApiMedium
    {
        return new OpenApiMedium()
            ->setPosition($medium->position->value)
            ->setFormatValue(MediumFormatValue::from($medium->format->value))
            ->setTracks($medium->tracks->toGeneric()->map($this->toOpenApiTrack(...))->toArray());
    }

    public function toOpenApiTrack(Track $track): OpenApiTrack
    {
        return new OpenApiTrack([
            'song_id' => $track->songId?->value,
            'title' => $track->title?->value,
        ])
            ->setTrackNo($track->trackNo->value);
    }

    public function toOpenApiReferencedSong(ReleaseReferencedSong $song): OpenApiReleaseReferencedSong
    {
        return new OpenApiReleaseReferencedSong([
            'song_id' => $song->songId,
        ])
            ->setMediumPosition($song->mediumPosition)
            ->setTrackNo($song->trackNo)
            ->setTitle($song->title);
    }
}
