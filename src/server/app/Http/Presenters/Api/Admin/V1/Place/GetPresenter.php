<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Place;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PlaceGetResponse;
use Place\Application\Admin\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PlaceGetResponse()->setPlace($this->converter->toOpenApiPlace($outputData->place)),
            200,
        );
    }
}
