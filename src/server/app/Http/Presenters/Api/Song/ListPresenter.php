<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongListResponse;
use ResultType\Result;
use Song\Application\Assemble\AssembledSong;
use Song\Application\UseCase\List\ListOutputData;
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
                new SongListResponse()->setSongs(
                    array_map(
                        fn (AssembledSong $song) => $this->converter->toOpenApiSong($song),
                        $outputData->songs,
                    ),
                ),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
