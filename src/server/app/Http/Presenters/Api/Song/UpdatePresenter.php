<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\SongUpdateResponse;
use ResultType\Result;
use Song\Application\UseCase\Update\UpdateOutputData;

class UpdatePresenter
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
                $song = $outputData->song;

                return [
                    new SongUpdateResponse()->setSong($this->converter->toOpenApiSong($song)),
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
