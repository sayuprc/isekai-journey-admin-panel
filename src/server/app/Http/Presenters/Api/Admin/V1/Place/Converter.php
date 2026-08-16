<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Place;

use OpenAPI\Admin\Client\Model\Place as OpenApiPlace;
use OpenAPI\Admin\Client\Model\PlaceKind as OpenApiPlaceKind;
use OpenAPI\Admin\Client\Model\PlaceKindValue;
use Place\Domain\Models\Place;

class Converter
{
    public function toOpenApiPlace(Place $place): OpenApiPlace
    {
        return new OpenApiPlace()
            ->setPlaceId($place->placeId->value)
            ->setName($place->name->value)
            ->setKind($this->toOpenApiPlaceKind($place));
    }

    private function toOpenApiPlaceKind(Place $place): OpenApiPlaceKind
    {
        return new OpenApiPlaceKind()
            ->setName($place->kind->getName())
            ->setValue(PlaceKindValue::from($place->kind->value));
    }
}
