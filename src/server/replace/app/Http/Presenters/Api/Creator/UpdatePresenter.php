<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\Update\UpdateOutputData;
use OpenAPI\Client\Model\CreatorUpdateResponse;
use OpenAPI\Client\Model\ErrorResponse;
use ResultType\Result;

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
                $creator = $outputData->creator;

                return [
                    new CreatorUpdateResponse()->setCreator($this->converter->toOpenApiCreator($creator)),
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
