<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongAttribute;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongAttributeListResponse;
use ResultType\Result;
use Song\Application\UseCase\ListAttribute\ListAttributeOutputData;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<ListAttributeOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListAttributeOutputData $outputData) => [
                new SongAttributeListResponse()->setAttributes(
                    array_map($this->converter->toOpenApiSongAttribute(...), $outputData->attributes),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
