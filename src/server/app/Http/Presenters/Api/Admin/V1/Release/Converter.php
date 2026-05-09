<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Release;

use OpenAPI\Client\Model\Release as OpenApiRelease;
use OpenAPI\Client\Model\ReleaseDistributionTypeValue;
use OpenAPI\Client\Model\ReleaseTypeValue;
use OpenAPI\Client\Model\TrackEntry as OpenApiTrackEntry;
use Release\Domain\Models\Release;
use Release\Domain\Models\TrackEntry;

class Converter
{
    public function toOpenApiRelease(Release $release): OpenApiRelease
    {
        return new OpenApiRelease()
            ->setReleaseId($release->releaseId->value)
            ->setTitle($release->title->value)
            ->setTypeValue(ReleaseTypeValue::from($release->type->value))
            ->setDistributionTypeValue(ReleaseDistributionTypeValue::from($release->distributionType->value))
            ->setReleasedOn($release->releasedOn->value->toMutable())
            ->setDescription($release->description->value)
            ->setIsDisplay($release->isDisplay)
            ->setTrackEntries($release->trackEntries->toGeneric()->map($this->toOpenApiTrackEntry(...))->toArray());
    }

    public function toOpenApiTrackEntry(TrackEntry $trackEntry): OpenApiTrackEntry
    {
        return new OpenApiTrackEntry()
            ->setSongId($trackEntry->songId->value)
            ->setTrackNo($trackEntry->trackNo->value);
    }
}
