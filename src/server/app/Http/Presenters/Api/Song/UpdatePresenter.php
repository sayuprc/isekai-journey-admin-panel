<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongUpdateResponse;
use ResultType\Result;
use Song\Application\UseCase\Update\UpdateOutputData;
use Support\UseCase\Error\UseCaseError;

class UpdatePresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<UpdateOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (UpdateOutputData $outputData) {
                $song = $outputData->song;

                return [
                    new SongUpdateResponse()->setSong($this->converter->toOpenApiSong($song)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
