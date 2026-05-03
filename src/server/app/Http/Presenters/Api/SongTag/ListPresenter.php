<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongTag;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongTagListResponse;
use ResultType\Result;
use Song\Application\UseCase\Tag\List\ListOutputData;
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
                new SongTagListResponse()->setTags(
                    array_map(
                        $this->converter->toOpenApiSongTag(...),
                        $outputData->tags,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
