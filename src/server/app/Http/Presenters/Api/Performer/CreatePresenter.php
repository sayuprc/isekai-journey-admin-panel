<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\PerformerCreateResponse;
use Performer\Application\UseCase\Create\CreateOutputData;
use ResultType\Result;

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
                $performer = $outputData->performer;

                return [
                    new PerformerCreateResponse()->setPerformer($this->converter->toOpenApiPerformer($performer)),
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
