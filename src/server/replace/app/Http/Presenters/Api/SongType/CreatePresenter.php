<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\SongType;

use App\Http\Responses\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\SongTypeCreateResponse;
use ResultType\Result;
use SongType\Application\UseCase\Create\CreateOutputData;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<CreateOutputData, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (CreateOutputData $outputData) {
                $songType = $outputData->songType;

                return [
                    new SongTypeCreateResponse()->setSongType($this->converter->toOpenApiSongType($songType)),
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

        return new JsonResponse($data, $status);
    }
}
