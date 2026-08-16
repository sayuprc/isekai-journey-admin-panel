<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Place;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PlaceSearchResponse;
use Place\Application\Admin\UseCase\Search\SearchOutputData;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PlaceSearchResponse()
                ->setPlaces(array_map($this->converter->toOpenApiPlace(...), $outputData->places))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
