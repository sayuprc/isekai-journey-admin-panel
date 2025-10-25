<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\List\ListOutputData;
use Creator\Domain\Models\Creator;
use OpenAPI\Client\Model\CreatorListResponse;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return new JsonResponse(
            new CreatorListResponse()
                ->setCreators(
                    array_map(
                        fn (Creator $creator) => $this->converter->toOpenApiCreator($creator),
                        $outputData->creators
                    )
                ),
        );
    }
}
