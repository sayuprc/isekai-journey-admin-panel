<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongTag;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongTagSearchResponse;
use ResultType\Result;
use Song\Application\UseCase\SearchTag\SearchOutputData;
use Song\Domain\Models\Tag\SongTag;
use Support\UseCase\Error\UseCaseError;

class SearchPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<SearchOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (SearchOutputData $outputData) => [
                new SongTagSearchResponse()
                    ->setTags(array_map(
                        fn (SongTag $songTag) => $this->converter->toOpenApiSongTag($songTag),
                        $outputData->tags,
                    ))
                    ->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
