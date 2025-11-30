<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\PerformerGetResponse;
use Performer\Application\UseCase\Get\GetOutputData;
use ResultType\Result;

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
                return [
                    new PerformerGetResponse()->setPerformer($this->converter->toOpenApiPerformer($outputData->performer)),
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
