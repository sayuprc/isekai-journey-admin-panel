<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use Creator\Application\UseCase\List\ListOutputData;
use Creator\Domain\Models\Creator;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorListResponse;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new CreatorListResponse()
                ->setCreators(
                    array_map(
                        fn (Creator $creator) => $this->converter->toOpenApiCreator($creator),
                        $outputData->creators
                    )
                ),
            200
        );
    }
}
