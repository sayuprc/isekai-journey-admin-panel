<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\UpdateSongTypeResponse;
use ResultType\Result;
use SongType\Application\UseCase\Update\UpdateOutputData;

class SongTypeUpdatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<UpdateOutputData, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (UpdateOutputData $outputData) {
                $songType = $outputData->songType;

                return [
                    new UpdateSongTypeResponse()->setSongType($this->converter->toOpenApiSongType($songType)),
                    200,
                ];
            },
            function (string $message) {
                return [
                    new ErrorResponse()->setMessage($message),
                    400,
                ];
            }
        );

        return response()->json($data, $status);
    }
}
