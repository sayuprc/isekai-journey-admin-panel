<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\SongGetResponse;
use ResultType\Result;
use Song\Application\Admin\UseCase\Get\GetOutputData;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (GetOutputData $outputData) {
                return [
                    new SongGetResponse()->setSong($this->converter->toOpenApiSong($outputData->song)),
                    200,
                ];
            },
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
