<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use Creator\Application\UseCase\Get\GetOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\CreatorGetResponse;
use OpenAPI\Client\Model\ErrorResponse;
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
                    new CreatorGetResponse()->setCreator($this->converter->toOpenApiCreator($outputData->creator)),
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
