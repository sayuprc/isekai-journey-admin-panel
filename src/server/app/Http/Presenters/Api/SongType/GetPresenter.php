<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\GetSongTypeResponse;
use ResultType\Result;
use SongType\Application\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<GetOutputData, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (GetOutputData $outputData) {
                $songType = $outputData->songType;

                return [
                    new GetSongTypeResponse()->setSongType($this->converter->toOpenApiSongType($songType)),
                    200,
                ];
            },
            function (string $message) {
                return [
                    new ErrorResponse()->setMessage($message),
                    404,
                ];
            }
        );

        return response()->json($data, $status);
    }
}
