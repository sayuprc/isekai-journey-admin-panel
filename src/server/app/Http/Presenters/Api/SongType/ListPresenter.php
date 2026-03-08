<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongTypeListResponse;
use ResultType\Result;
use Song\Application\UseCase\ListType\ListTypeOutputData;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<ListTypeOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListTypeOutputData $outputData) => [
                new SongTypeListResponse()->setTypes(
                    array_map(
                        fn (SongType $type) => $this->converter->toOpenApiSongType($type),
                        $outputData->types,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
