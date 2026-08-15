<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Place;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PlaceUpdateResponse;
use Place\Application\Admin\UseCase\Update\UpdateOutputData;

class UpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(UpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PlaceUpdateResponse()->setPlace($this->converter->toOpenApiPlace($outputData->place)),
            200,
        );
    }
}
