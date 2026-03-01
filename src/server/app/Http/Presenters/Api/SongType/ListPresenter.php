<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongTypeListResponse;
use ResultType\Result;
use SongType\Application\UseCase\List\ListOutputData;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\UseCaseError;

class ListPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<ListOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (ListOutputData $outputData) => [
                new SongTypeListResponse()->setSongTypes(
                    array_map(
                        fn (SongType $songType) => $this->converter->toOpenApiSongType($songType),
                        $outputData->songTypes,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
