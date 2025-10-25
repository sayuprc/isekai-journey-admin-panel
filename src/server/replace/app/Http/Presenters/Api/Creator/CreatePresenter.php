<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\Create\CreateOutputData;
use OpenAPI\Client\Model\CreatorCreateResponse;
use OpenAPI\Client\Model\ErrorResponse;
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
                $creator = $outputData->creator;

                return [
                    new CreatorCreateResponse()->setCreator($this->converter->toOpenApiCreator($creator)),
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
