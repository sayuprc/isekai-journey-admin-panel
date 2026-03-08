<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongSearchResponse;
use ResultType\Result;
use Song\Application\UseCase\Search\SearchOutputData;
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
                new SongSearchResponse()
                    ->setSongs(array_map($this->converter->toOpenApiSong(...), $outputData->songs))
                    ->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
