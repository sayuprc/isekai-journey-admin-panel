<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Place;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\PlaceCreateResponse;
use Place\Application\Admin\UseCase\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(CreateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PlaceCreateResponse()->setPlace($this->converter->toOpenApiPlace($outputData->place)),
            200,
        );
    }
}
