<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\SongCreateResponse;
use ResultType\Result;
use Song\Application\UseCase\Create\CreateOutputData;

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
                $song = $outputData->song;

                return [
                    new SongCreateResponse()->setSong($this->converter->toOpenApiSong($song)),
                    200,
                ];
            },
            function (string $message) {
                return [
                    new ErrorResponse()->setMessage($message),
                    400,
                ];
            },
        );

        return response()->json($data, $status);
    }
}
