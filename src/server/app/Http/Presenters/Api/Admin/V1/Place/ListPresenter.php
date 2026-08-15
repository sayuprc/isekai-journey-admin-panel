<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Place;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PlaceListResponse;
use Place\Application\Admin\UseCase\List\ListOutputData;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PlaceListResponse()->setPlaces(array_map($this->converter->toOpenApiPlace(...), $outputData->places)),
            200,
        );
    }
}
