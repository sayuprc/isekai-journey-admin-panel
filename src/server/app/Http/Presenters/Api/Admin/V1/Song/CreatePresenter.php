<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongCreateResponse;
use ResultType\Result;
use Song\Application\Admin\UseCase\Create\CreateOutputData;
use Support\UseCase\Error\UseCaseError;

class CreatePresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<CreateOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (CreateOutputData $outputData) {
                $song = $outputData->song;

                return [
                    new SongCreateResponse()->setSong($this->converter->toOpenApiSong($song)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
